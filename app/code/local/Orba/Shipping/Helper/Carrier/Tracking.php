<?php
class Orba_Shipping_Helper_Carrier_Tracking extends Mage_Core_Helper_Abstract {
    
    protected $_helper = null;    
    
    //{{{ 
    /**
     * helper for specific carrier type
     * @param Mage_Core_Helper_Abstract $helper
     * @return 
     */
    public function setHelper($helper) {
        $this->_helper = $helper;
    }
    //}}}
    /**
     * @param Mage_Sales_Model_Order_Shipment_Track $track
     * @param string $shipmentIdMessage
     * @param array $carrierMessage
     * @param string $status
     * @return boolean
     */
     protected function _addComment($track,$shipmentIdMessage,$carrierMessage,$status) {
        $comment = $this->__($this->_helper->getHeader()) . PHP_EOL;

		/* @var $shipment Mage_Sales_Model_Order_Shipment */
		$shipment		= $track->getShipment();
		$poId			= $shipment->getUdpoId();

		$comment .= $shipmentIdMessage;
		$carrierComment	= $this->_getPoOrderCommentsHistory($poId, $comment);
		$carrierMessage = array_reverse(array_unique($carrierMessage));
		foreach ($carrierMessage as $singleMessage) {
			$comment .= $singleMessage;
		}
		
		$comment = trim($comment);
		//Add Dhl T&T Comment to PO
		$carrierComment->setParentId($poId)
				->setComment($comment)
				->setCreatedAt(now())
				->setIsVisibleToVendor(true)
				->setUsername('API');
		
		//Add Dhl T&T Comment to related Shipment History
		$this->_addShipmentComment(
			$track->getShipment(),
			$comment,
			$status
		);
		try {
			$carrierComment->save();
		} catch (Exception $e) {
			Mage::logException($e);
			return false;
		}
		
     }
    protected function _addShipmentComment($shipment, $comment, $status = false, $visibleToVendor=true, $isVendorNotified=false, $isCustomerNotified=false)
    {		
		$commentModel = Mage::getResourceModel('sales/order_shipment_comment_collection')
			->setShipmentFilter($shipment->getId())
			->addFieldToFilter('comment', array('like' => '%'. $this->_helper->getHeader() . '%'))
			->getFirstItem();
		$commentModel->setParentId($shipment->getId())
			->setComment($comment)
			->setIsCustomerNotified($isCustomerNotified)
			->setIsVendorNotified($isVendorNotified)
			->setIsVisibleToVendor($visibleToVendor)
			->setUdropshipStatus($status)
			->setCreatedAt(now());
        $commentModel->save();
        return $commentModel;
    }
	protected function _getPoOrderCommentsHistory($poId, $commentFilter = false, $firstItem = true) {
		($commentFilter) ? $commentFilter : $this->_helper->getHeader();
		$comments = Mage::getModel('udpo/po_comment')
						->getCollection()
						->setPoFilter($poId)
						->addFieldToFilter('comment', array('like' => '%'. $commentFilter . '%'));
				
		if ($firstItem) {
			$comments = $comments->getFirstItem();
		}		
		
		return $comments;
	}
	/**
	 * Collect Tracking Data from Web Api Calls
	 * 
	 * @param array $trackIds
	 * 
	 * @return boolean
	 */
	public function collectTracking($trackIds)
	{
		if (!$this->_helper->isActive()) {
			$this->_helper->_log('Service is not Active');
			return false;
		}
		$carrierClient = $this->_helper->startClient();
		$processTracks = array();
		foreach ($trackIds as $_trackId => $_tracks) {
			foreach ($_tracks as $_track) {
			    // processing
			    $this->_process($carrierClient,$_track);
			    
				if (empty($processTracks[$_track->getParentId()])) {
					$processTracks[$_track->getParentId()] = array();
				}
				$processTracks[$_track->getParentId()][] = $_track;
			}
		}
		//Process all Collected Shipments and update Shipment Status
		$result = $this->_processShipmentTracks($processTracks);
		return true;
	}

	protected function _processShipmentTracks($shipmentTracks)
	{
		foreach ($shipmentTracks as $_sTracksId => $_sTracks) {
			$tracksCount = count($shipmentTracks[$_sTracksId]);
			foreach ($_sTracks as $_track) {
				switch ($tracksCount) {
					case Zolago_Dropship_Helper_Data::TRACK_SINGLE:
						$this->_processOrder($_track);
						break;
					default:
						$this->_processOrder($_sTracks, true);
						break;
				}
			}
		}		
	}
	
	protected function _processOrder($_sTracks, $multiple = false)
	{
		$completeOrder = true;
		
		if ($multiple) {
			//Check if all Shipments are Delivered and Update Order Status
			foreach ($_sTracks as $sTrack) {
				if ($sTrack->getUdropshipStatus() !== Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED) {
					$completeOrder = false;
				}
			}
		} else {
			$sTrack = $_sTracks;
			$shipmentStatus = $sTrack->getUdropshipStatus();
			$shipment = $sTrack->getShipment();
			switch ($shipmentStatus) {
				case Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED:
					$this->_setOrderCompleteState($shipment);
					break;
				default:
					$completeOrder = false;
					break;
			}
		}
		
		if ($completeOrder) {
			//Set Order Status based on all related shipments
			$this->_setOrderCompleteState($sTrack->getShipment());
		}
		
		return $this;
	}
	
	protected function _setOrderCompleteState($shipment)
	{
		$order = $shipment->getOrder();
		$order->setStatus(Mage_Sales_Model_Order::STATE_COMPLETE)
			->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED);					
		$poOrderId = $shipment->getUdpoId();
		$poOrder = Mage::getModel('udpo/po')->load($poOrderId);
		$poOrder->setStatus(Mage_Sales_Model_Order::STATE_COMPLETE)
			->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED);
		
		try {
			$order->save();
		} catch (Exception $e) {
			Mage::logException($e);
			return false;
		}

		try {
			$poOrder->save();
		} catch (Exception $e) {
			Mage::logException($e);
			return false;
		}
	}
	

}