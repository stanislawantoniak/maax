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
	 * @return Unirgy_Dropship_Model_Mysql4_Vendor_Collection
	 */
	public function getVendorColleciton($limit=false) {
		if(!$this->hasData("vendor_collection")){
            $localVendorId = Mage::helper('udropship/data')->getLocalVendorId();
			$collection = Mage::getResourceModel('udropship/vendor_collection');
			/* @var $collection Unirgy_Dropship_Model_Mysql4_Vendor_Collection */
			$status = array(Unirgy_Dropship_Model_Source::VENDOR_STATUS_ACTIVE);
			if (Mage::app()->getWebsite()->getIsPreviewWebsite()) {
			    $status[] = Unirgy_Dropship_Model_Source::VENDOR_STATUS_INACTIVE;
			}
			$collection->addStatusFilter($status);
            $collection->addFieldToFilter('vendor_type', Zolago_Dropship_Model_Vendor::VENDOR_TYPE_BRANDSHOP);
            $collection->addFieldToFilter('vendor_id', array('neq' => $localVendorId));

            $collection->setOrder("sequence", Varien_Data_Collection::SORT_ORDER_ASC);
            $collection->setOrder("vendor_name", Varien_Data_Collection::SORT_ORDER_ASC);

			if($limit) {
				$collection->setPageSize($limit);
			}

			// Load serialized data
			foreach($collection as $vendor){
				Mage::helper('udropship')->loadCustomData($vendor);
			}
			$this->setData("vendor_collection", $collection);
		}
		return $this->getData("vendor_collection");
	}

	public function addDummyVendorsToCollection(&$collection,$totalBrandsBlocks) {
		$collectionSize = $collection->count();

		$dummyVendor = new Zolago_Dropship_Model_Vendor();
		$dummyVendor
			->setDummy(true)
			->setVendorResizedLogoUrl($this->getSkinUrl('images/brand_comming.png'))
			->setVendorName(Mage::helper("zolagomodago")->__("Next soon"));

		$maxDummies = $totalBrandsBlocks - $collectionSize;

		for ($i = 0; $i < $maxDummies; $i++) {
			$collection->addItem($dummyVendor);
		}

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
	 * @return string
	 */
	public function getViewMoreUrl() {
		return $this->getUrl("modago/brands");
	}
	
} 