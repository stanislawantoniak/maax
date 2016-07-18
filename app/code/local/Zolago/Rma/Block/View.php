<?php
class Zolago_Rma_Block_View extends Zolago_Rma_Block_Abstract
{
	
	const CMS_PENDING = "rma_status_pending_currier_message";
	const CMS_PENDING_ACCEPT = "rma_status_pending_message";
	const CMS_PENDING_BOOKING = "rma_status_pending_booking_message";

	/**
	 * @param Zolago_Rma_Model_Rma $rma
	 * @return string
	 */
	public function getStatusCustomerText(Zolago_Rma_Model_Rma $rma) {
		return $this->__($rma->getStatusCustomerNotes() ? $rma->getStatusCustomerNotes() : $rma->getRmaStatusName());
	}
	
	/**
	 * 
	 * @param Zolago_Rma_Model_Rma $rma
	 * @return bool
	 */
	public function isPendingPickup(Zolago_Rma_Model_Rma $rma) {
		return $rma->getRmaStatus()==Zolago_Rma_Model_Rma_Status::STATUS_PENDING_PICKUP;
	}

    /**
     *
     * @param Zolago_Rma_Model_Rma $rma
     * @return bool
     */
    public function isPendingCourierBooking(Zolago_Rma_Model_Rma $rma) {
        return $rma->getRmaStatus()==Zolago_Rma_Model_Rma_Status::STATUS_PENDING_COURIER;
    }

	/**
	 *
	 * @param Zolago_Rma_Model_Rma $rma
	 * @return bool
	 */
	public function isPending(Zolago_Rma_Model_Rma $rma) {
		return $rma->getRmaStatus()==Zolago_Rma_Model_Rma_Status::STATUS_PENDING;
	}
	
	/**
	 * @todo implement
	 * @param Zolago_Rma_Model_Rma $rma
	 * @return string | null
	 */
	public function getPdfUrl(Zolago_Rma_Model_Rma $rma) {
		$helperTrack = Mage::helper('zolagorma/tracking');
        /** @var Zolago_Dhl_Helper_Data $helperDhl */
		$helperDhl = Mage::helper('orbashipping/carrier_dhl');
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		
		$track = $helperTrack->getRmaTrackingForCustomer($rma, $customer);
		if($track && $track->getId()){
			$dhlFile = $helperDhl->getRmaDocument($track);
			if(file_exists($dhlFile)){
				return $this->getUrl("*/*/pdf", array("id"=>$rma->getId()));
			}
		}
		
		return null;
	}
	
	/**
	 * @param Zolago_Rma_Model_Rma $rma
	 * @return array
	 */
	public function getMonits(Zolago_Rma_Model_Rma $rma) {
		$out = array();
		if($this->getIsSuccessPage($rma)){
			$out[] = $message = $this->getSuccessMessage($rma);
		}
		if($this->isPendingPickup($rma)){
			$out[] = Mage::getModel("cms/block")->
				load(self::CMS_PENDING)->getContent();
		}
		if($this->isPending($rma)){
			$out[] = Mage::getModel("cms/block")->
			load(self::CMS_PENDING_ACCEPT)->getContent();
		}
		if($this->isPendingCourierBooking($rma)) {
			$out[] = Mage::getModel("cms/block")->
			load(self::CMS_PENDING_BOOKING)->getContent();
		}
		return $out;
	}
	
	/**
	 * @todo recogenize type of RMA
	 * @param Zolago_Rma_Model_Rma $rma
	 * @return string
	 */
	public function getSuccessMessage(Zolago_Rma_Model_Rma $rma) {
		return Mage::getStoreConfig('zosrma/message/customer_success');
	}
	
	/**
	 * Is 'success' emulation
	 * @param Zolago_Rma_Model_Rma
	 * @return type
	 */
	public function getIsSuccessPage(Zolago_Rma_Model_Rma $rma) {
		return $rma->getJustCreated();
	}
}
