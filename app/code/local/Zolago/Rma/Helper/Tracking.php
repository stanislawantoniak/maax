<?php
class Zolago_Rma_Helper_Tracking extends Orba_Shipping_Helper_Carrier_Tracking {

	/**
	 * @param Zolago_Rma_Model_Rma $rma
	 * @param Mage_Customer_Model_Customer $customer
	 * @return Zolago_Rma_Model_Rma_Track
	 */
	public function getRmaTrackingForCustomer(Zolago_Rma_Model_Rma $rma, 
			Mage_Customer_Model_Customer $customer) {
		
		// No customer-owned RMA
		if($rma->getCustomerId()!=$customer->getId()){
			return false;
		}
		
		$collection = Mage::getResourceModel("urma/rma_track_collection");
		/* @var $collection Zolago_Rma_Model_Resource_Rma_Track_Collection */
		$collection->addCustomerFilter();
		$collection->addFieldToFilter('parent_id', $rma->getId());
		$collection->setOrder("created_at", "desc");
		
		return $collection->getFirstItem();
	}
	
    /**
     * @param Zolago_Rma_Model_Rma_Track $track
     * @param string $shipmentIdMessage
     * @param array $carrierMessage
     * @param string $status
     * @return bool
     */
    public function addComment($track,$shipmentIdMessage,$carrierMessage,$status) {
        $comment = $this->__($this->_helper->getHeader()) . PHP_EOL;
        /* @var $shipment Mage_Sales_Model_Order_Shipment */
        $rma = $track->getRma();
        $comment .= $shipmentIdMessage;
        $carrierMessage = array_reverse(array_unique($carrierMessage));
        foreach ($carrierMessage as $singleMessage) {
            $comment .= $singleMessage;
        }
        $rmaComment = Mage::getModel("urma/rma_comment");
        $comment = trim($comment);
        //Add Dhl T&T Comment to PO
        $data = array(
                    "parent_id"				=> $rma->getId(),
                    "is_customer_notified"	=> 0,
                    "is_visible_on_front"	=> 0,
                    "comment"				=> $comment,
                    "created_at"			=> Varien_Date::now(),
                    "is_vendor_notified"	=> 1,
                    "is_visible_to_vendor"	=> 1,
                    "udropship_status"		=> null,
                    "username"				=> 'API',
                    "rma_status"			=> $rma->getUdropshipStatus()
                );
        try {
            $rmaComment->setRma($rma)
            ->addData($data)
            ->setSkipSettingName(true)
            ->save();
            /* @var $model ZolagoOs_Rma_Model_Rma_Comment */

            Mage::dispatchEvent("zolagorma_rma_comment_added", array(
                                    "rma"		=> $rma,
                                    "comment"	=> $rmaComment,
                                    "notify"	=> false
                                ));

        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }

    }

    /**
     * change rma status if possible
     *
     * @param array $shipmentTracks
     */
    protected function _processShipmentTracks($shipmentTracks)
    {
        $helper = Mage::helper('zolagorma');
        foreach ($shipmentTracks as $_rmaId => $_sTracks) {
            $shipped = true;
            $track = null;
            foreach ($_sTracks as $_track) {
                $status = $_track->getUdropshipStatus();
                $creator = $_track->getTrackCreator();
                if ($creator == Zolago_Rma_Model_Rma_Track::CREATOR_TYPE_CUSTOMER) {
                    if ($status == ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_PENDING) {
                        $shipped = false;
                    } else {
                        $track = $_track;
                    }
                }
            }
            if ($shipped) {
                $rma = Mage::getModel('urma/rma')->load($_rmaId);
                if ($rma) {
                    if ($rma->getRmaStatus() == Zolago_Rma_Model_Rma_Status::STATUS_PENDING_PICKUP && $track) {
                        $rma->setRmaStatus(Zolago_Rma_Model_Rma_Status::STATUS_PENDING_DELIVERY);
                        Mage::dispatchEvent("zolagorma_rma_track_status_change", array(
							"track"      => $track,
							"rma"        => $rma,
							"new_status" => $helper->__($helper->getRmaStatusName(Zolago_Rma_Model_Rma_Status::STATUS_PENDING_DELIVERY)),
							"old_status" => $helper->__($helper->getRmaStatusName(Zolago_Rma_Model_Rma_Status::STATUS_PENDING_PICKUP)),
						));
                        $rma->save();
                    }
                }
            }
        }
    }

}