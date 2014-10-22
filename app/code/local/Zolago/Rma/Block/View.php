<?php
class Zolago_Rma_Block_View extends Zolago_Rma_Block_Abstract
{
	
	const CMS_PENDING = "rma_status_pending_currier_message";
	
	/**
	 * 
	 * @param Zolago_Rma_Model_Rma $rma
	 * @return bool
	 */
	public function isPendingPickup(Zolago_Rma_Model_Rma $rma) {
		return $rma->getRmaStatus()==Zolago_Rma_Model_Rma_Status::STATUS_PENDING_PICKUP;
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
	 * @return string | null
	 */
	public function getMonitMessage(Zolago_Rma_Model_Rma $rma) {
		$message = null;
		if($this->getIsSuccessPage($rma)){
			$message = $this->getSuccessMessage($rma);
		}else if($this->isPendingPickup($rma)){
			$message = Mage::getModel("cms/block")->
				load(self::CMS_PENDING)->getContent();
		}
		return $message;
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
