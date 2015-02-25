<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 25.04.14
 */

class Zolago_Modago_Block_Home_Vendor extends Mage_Core_Block_Template
{
	/*
	 * Potrzebuję tutaj kolekcję, która będzie zawierać:
	 * - obrazek maksymalnie 130x74px
	 * - url gdzie ma kierować element listy lub klucz i nazwę routa
	 * - tekst - nazwę vendora
	 *
	 * Dodatkowo URL do ZOBACZ WIĘCEJ MAREK
	 */
	
	/**
	 * @todo add website filter (implement before) & cache results
	 * @return Unirgy_Dropship_Model_Mysql4_Vendor_Collection
	 */
	public function getVendorColleciton() {
		if(!$this->hasData("vendor_collection")){
			$collection = Mage::getResourceModel('udropship/vendor_collection');
			/* @var $collection Unirgy_Dropship_Model_Mysql4_Vendor_Collection */
			$collection->addStatusFilter(Unirgy_Dropship_Model_Source::VENDOR_STATUS_ACTIVE);
//			$collection->setOrder("vendor_name");
            $collection->setOrder("sequence", "ASC");
            $collection->setPageSize(12);
			// Load serialized data
			foreach($collection as $vendor){
				Mage::helper('udropship')->loadCustomData($vendor);
			}
			$this->setData("vendor_collection", $collection);
		}
		return $this->getData("vendor_collection");
	}
	
	/**
	 * @param Unirgy_Dropship_Model_Vendor $vendor
	 * @return string
	 */
	public function getVendorName(Unirgy_Dropship_Model_Vendor $vendor) {
		return $vendor->getVendorName();
	}
	
	/**
	 * @param Unirgy_Dropship_Model_Vendor $vendor
	 * @return string | null
	 */
	public function getVendorMarkUrl(Unirgy_Dropship_Model_Vendor $vendor) {
		return $vendor->getFileUrl('logo');
	}
	
	/**
	 * @param Unirgy_Dropship_Model_Vendor $vendor
	 * @return string | null
	 */
	public function getVendorResizedLogoUrl(Unirgy_Dropship_Model_Vendor $vendor, 
			$width=130, $height=74) {
        /* @var $zolagodropship Zolago_Dropship_Helper_Data */
		$zolagodropship = Mage::helper("zolagodropship");

		return $zolagodropship->getVendorLogoResizedUrl($vendor, $width, $height);
	}
	
	/**
	 * @param Unirgy_Dropship_Model_Vendor $vendor
	 * @return string
	 */
	public function getVendorBaseUrl(Unirgy_Dropship_Model_Vendor $vendor) {
		return Mage::helper("umicrosite")->getVendorBaseUrl($vendor);
	}
	
	/**
	 * @todo implement controller action - now is 404
	 * @return string
	 */
	public function getViewMoreUrl() {
		return $this->getUrl("udropship/index/vendors");
	}
	
} 