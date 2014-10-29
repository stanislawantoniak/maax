<?php
class Zolago_Rma_Block_View extends Zolago_Rma_Block_Abstract
{
	
	const CMS_PENDING = "rma_status_pending_currier_message";
	
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
    public function isAccepted(Zolago_Rma_Model_Rma $rma) {
        Mage::log($rma->getRmaStatus());
        return $rma->getRmaStatus()==Zolago_Rma_Model_Rma_Status::STATUS_ACCEPTED;
    }
	
	/**
	 * @todo implement
	 * @param Zolago_Rma_Model_Rma $rma
	 * @return string | null
	 */
	public function getPdfUrl(Zolago_Rma_Model_Rma $rma) {
		$helperTrack = Mage::helper('zolagorma/tracking');
		$helperDhl = Mage::helper('zolagodhl');
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
		return $out;
	}
	
	/**
	 * @todo recogenize type of RMA
	 * @param Zolago_Rma_Model_Rma $rma
	 * @return string
	 */
	public function getSuccessMessage(Zolago_Rma_Model_Rma $rma) {
		return Mage::getStoreConfig('urma/message/customer_success');
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
