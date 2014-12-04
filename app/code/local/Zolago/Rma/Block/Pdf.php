<?php
class Zolago_Rma_Block_Pdf extends Zolago_Rma_Block_Abstract {
	protected $rma;
	protected $storeViewOrig;
	protected $weekdays;

	public function __construct() {
		$this->rma = $this->getRma();
		$orderStoreId = $this->getPo()->getStoreId();
		$orderLangCode =  Mage::app()->getStore($orderStoreId)->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
		Mage::getSingleton('core/translate')->setLocale($orderLangCode)->init('frontend', true);
		$this->weekdays = Mage::app()->getLocale()->setLocale($orderLangCode)->getOptionWeekdays();
	}

	public function getLogo() {
		return $this->getSkinUrl("images/logo_black.png");
	}

	public function getVendorName() {
		return $this->getVendor()->getVendorName();
	}

	public function getVendor() {
		return $this->getPo()->getVendor();
	}

	public function getPo() {
		return $this->rma->getPo();
	}

	public function getVendorAddress() {
		return $this->getVendor()->getFormatedAddress('html');
	}
}