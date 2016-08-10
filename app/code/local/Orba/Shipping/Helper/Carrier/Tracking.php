<?php
class Orba_Shipping_Helper_Carrier_Tracking extends Mage_Core_Helper_Abstract {
    
    protected $_helper = null;    
    

    //{{{ 
    /**
     * list of carriers allowed to autotracking
     * @return array
     */
    public function getTrackingCarriersList() {
        $out = array();
        if (Mage::helper('orbashipping/carrier_dhl')->isActive()) {
            $out[] = Orba_Shipping_Model_Carrier_Dhl::CODE;
        }
        if (Mage::helper('orbashipping/carrier_ups')->isActive()) {
            $out[] = Orba_Shipping_Model_Carrier_Ups::CODE;
        }
        if (Mage::helper('orbashipping/packstation_inpost')->isActive()) {
            $out[] = Orba_Shipping_Model_Packstation_Inpost::CODE;
        }
        if (Mage::helper('orbashipping/post')->isActive()) {
            $out[] = Orba_Shipping_Model_Post::CODE;
        }
        if (Mage::helper('orbashipping/carrier_gls')->isActive()) {
            $out[] = Orba_Shipping_Model_Carrier_Gls::CODE;
        }
		if (Mage::helper('orbashipping/carrier_dpd')->isActive()) {
			$out[] = Orba_Shipping_Model_Carrier_Dpd::CODE;
		}
        return $out;
    }
    //}}}

    //{{{ 
    /**
     * helper for specific carrier type
     * @param Mage_Core_Helper_Abstract $helper
     * @return 
     */
    public function setHelper($helper) {
        $helper->setTrackingHelper($this);
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
     public function addComment($track,$shipmentIdMessage,$carrierMessage,$status) {
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
			    $this->_helper->process($carrierClient,$_track);
			    
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
			foreach ($_sTracks as $_track) {
				$this->_processOrder($_track);
			}
		}		
	}

	protected function _processOrder($_sTracks) {
		$sTrack = $_sTracks;
		$shipment = $sTrack->getShipment();
		$shipmentStatus = (int)$shipment->getUdropshipStatus();
		$poOrderId = $shipment->getUdpoId();
		
		/** @var Zolago_Po_Model_Po $po */
		$po = Mage::getModel('zolagopo/po')->load($poOrderId);
		if ($shipmentStatus == ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_RETURNED) {
			Mage::dispatchEvent('shipment_returned', array('shipment' => $shipment));
		}
		
		if ($shipmentStatus == ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_DELIVERED) {
			$po->getStatusModel()->changeStatus($po, Zolago_Po_Model_Po_Status::STATUS_DELIVERED);
		}
		if ($shipmentStatus == ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_SHIPPED) {
			$po->getStatusModel()->changeStatus($po, Zolago_Po_Model_Po_Status::STATUS_SHIPPED);
		}
		
		return $this;
	}

	private function _setOrderState($po) {
		/** @var Zolago_Po_Model_Po $helper */
		$helper = Mage::getModel('udpo/po');
		$helper->setOrderState($po);
	}

}