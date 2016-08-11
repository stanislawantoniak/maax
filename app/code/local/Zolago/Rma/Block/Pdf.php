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
		$weekdays = Zend_Locale::getTranslationList("Days",$orderLangCode);
		$this->weekdays = array_values($weekdays['format']['wide']);
	}

	public function getLogo() {	    
	    $logo = Mage::getStoreConfig('design/rma_document/rma_logo');
	    if ($logo) {
	        return Mage::getDesign()->getSkinBaseDir().DS.$logo;
        } 
        return null;
	}

	public function getVendorName() {
		return $this->getVendor()->getVendorName();
	}

    /**
     * @return Zolago_Dropship_Model_Vendor
     */
	public function getVendor() {
		return $this->getPo()->getVendor();
	}

	public function getPo() {
		return $this->rma->getPo();
	}

//	public function getVendorAddress() {
//		$address = trim($this->getVendor()->getFormatedAddress('html'));
//		$br_array = ["<br/>","<br>","<br />"];
//		for($i = 4; $i <=6; $i++) {
//			$substr = substr($address,0,$i);
//			if(in_array($substr,$br_array)) {
//				return substr($address,$i);
//			}
//		}
//		return $address;
//	}

    public function getDeliveryAddress() {
        $vendor = $this->getVendor();
        $address = $vendor->getRmaAddress();
        $text  = $address['name'] . '<br/>';
        $text .= $address['street'] . '<br/>';
        $text .= $address['postcode'] . ' '. $address['city'];
        return $text;
    }
}