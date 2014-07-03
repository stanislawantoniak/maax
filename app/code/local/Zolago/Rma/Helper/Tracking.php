<?php
class Zolago_Rma_Helper_Tracking extends Zolago_Dhl_Helper_Tracking {


    /**
     * @param Zolago_Rma_Model_Rma_Track $track
     * @param string $shipmentIdMessage
     * @param array $dhlMessage
     * @param string $status
     */
    protected function _addComment($track,$shipmentIdMessage,$dhlMessage,$status) {
        $comment = $this->__(Zolago_Dhl_Helper_Data::DHL_HEADER) . PHP_EOL;
        /* @var $shipment Mage_Sales_Model_Order_Shipment */
        $rma = $track->getRma();
        $comment .= $shipmentIdMessage;
        $dhlMessage = array_reverse(array_unique($dhlMessage));
        foreach ($dhlMessage as $singleMessage) {
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
            /* @var $model Unirgy_Rma_Model_Rma_Comment */

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
                    if ($status == Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING) {
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