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

		if ($multiple) {
			//Check if all Shipments are Delivered and Update Order Status
			foreach ($_sTracks as $sTrack) {
                $shipment = $sTrack->getShipment();
                $shipmentStatus = (int)$shipment->getUdropshipStatus();
                $poOrderId = $shipment->getUdpoId();

                /*@var $poOrder  Unirgy_DropshipPo_Model_Po */
                $poOrder = Mage::getModel('zolagopo/po')->load($poOrderId);

                if($shipmentStatus == Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_RETURNED){
                    Mage::dispatchEvent('shipment_returned',array('shipment'=>$shipment));
                }
                if($shipmentStatus == Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED){

                    $poOrder->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED);

                    try {
                        $poOrder->save();
                    } catch (Exception $e) {
                        Mage::logException($e);
                        return false;
                    }
                }
                $this->_setOrderState($poOrder);
			}
		} else {
            $sTrack = $_sTracks;
            $shipment = $sTrack->getShipment();
            $shipmentStatus = (int)$shipment->getUdropshipStatus();
            $poOrderId = $shipment->getUdpoId();

            /*@var $poOrder  Unirgy_DropshipPo_Model_Po */
            $poOrder = Mage::getModel('udpo/po')->load($poOrderId);
            if($shipmentStatus == Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_RETURNED){
                Mage::dispatchEvent('shipment_returned',array('shipment'=>$shipment));
            }
            if($shipmentStatus == Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED){

                $poOrder->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED);

                try {
                    $poOrder->save();
                } catch (Exception $e) {
                    Mage::logException($e);
                    return false;
                }
            }
            $this->_setOrderState($poOrder);
		}



		return $this;
	}

    private function _setOrderState($po)
    {
        Mage::getModel('udpo/po')
            ->setOrderState($po);
    }

}