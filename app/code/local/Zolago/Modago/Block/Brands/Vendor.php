<?php

class Zolago_Modago_Block_Brands_Vendor extends Mage_Core_Block_Template
{
	/**
	 * @return Unirgy_Dropship_Model_Mysql4_Vendor_Collection
	 */
	public function getVendorCollection() {
		if(!$this->hasData("vendor_collection")){
            $localVendorId = Mage::helper('udropship/data')->getLocalVendorId();
			$collection = Mage::getResourceModel('udropship/vendor_collection');
			/* @var $collection Unirgy_Dropship_Model_Mysql4_Vendor_Collection */
			$collection->addStatusFilter(Unirgy_Dropship_Model_Source::VENDOR_STATUS_ACTIVE);
            $collection->addFieldToFilter('vendor_type', Zolago_Dropship_Model_Vendor::VENDOR_TYPE_BRANDSHOP);

            $collection->setOrder("sequence", Varien_Data_Collection::SORT_ORDER_ASC);
            $collection->setOrder("vendor_name", Varien_Data_Collection::SORT_ORDER_ASC);

            $collection->addFieldToFilter('vendor_id', array('neq' => $localVendorId));
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

	
} 