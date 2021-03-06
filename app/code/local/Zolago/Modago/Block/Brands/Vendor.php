<?php

class Zolago_Modago_Block_Brands_Vendor extends Mage_Core_Block_Template
{
	/**
	 * @return ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Collection
	 */
	public function getVendorCollection($addDummies=true) {
		if(!$this->hasData("vendor_collection")){
            $localVendorId = Mage::helper('udropship/data')->getLocalVendorId();
			$collection = Mage::getResourceModel('udropship/vendor_collection');
			/* @var $collection ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Collection */
			$status = array(ZolagoOs_OmniChannel_Model_Source::VENDOR_STATUS_ACTIVE);
			if (Mage::app()->getWebsite()->getIsPreviewWebsite()) {
			    $status[] = ZolagoOs_OmniChannel_Model_Source::VENDOR_STATUS_INACTIVE;
			}
			$collection->addStatusFilter($status);			
            $collection->addFieldToFilter('vendor_type', Zolago_Dropship_Model_Vendor::VENDOR_TYPE_BRANDSHOP);

            $collection->setOrder("sequence", Varien_Data_Collection::SORT_ORDER_ASC);
            $collection->setOrder("vendor_name", Varien_Data_Collection::SORT_ORDER_ASC);

            $collection->addFieldToFilter('vendor_id', array('neq' => $localVendorId));
			// Load serialized data
			/** @var Zolago_Dropship_Helper_Data $helper */
			$helper = Mage::helper('udropship');
			foreach($collection as $vendor){
				$helper->loadCustomData($vendor);
			}

			if($addDummies) {
				$this->addDummyVendorsToCollection($collection);
			}
			
			$this->setData("vendor_collection", $collection);
		}
		return $this->getData("vendor_collection");
	}

	private function addDummyVendorsToCollection(&$collection) {
		$collectionSize = $collection->getSize();
		$dummyVendorsCount = array();
		$dummyVendorsCount[8] = 8 - ($collectionSize % 8); //8 = maximum number in row
		$dummyVendorsCount[7] = 7 - ($collectionSize % 7);
		$dummyVendorsCount[6] = 6 - ($collectionSize % 6);
		$dummyVendorsCount[5] = 5 - ($collectionSize % 5);
		$dummyVendorsCount[4] = 4 - ($collectionSize % 4);
		$dummyVendorsCount[3] = 3 - ($collectionSize % 3);
		$dummyVendorsCount[2] = 2 - ($collectionSize % 2);

		$dummyVendor = new Zolago_Dropship_Model_Vendor();
		$dummyVendor
			->setDummy(true)
			->setVendorResizedLogoUrl($this->getSkinUrl('images/brand_comming.png'))
			->setVendorName(Mage::helper("zolagomodago")->__("Next soon"));

		$maxDummies = max($dummyVendorsCount);

		for ($i = 1; $i <= $maxDummies; $i++) {
			$classesToAdd = "brands-dummy";

			$classesToAdd .= $dummyVendorsCount[2] >= $i ? ' visible-xxs' : '';
			$classesToAdd .= $dummyVendorsCount[3] >= $i ? ' visible-xs' : '';
			$classesToAdd .= $dummyVendorsCount[4] >= $i ? ' visible-xss-landscape' : '';
			$classesToAdd .= $dummyVendorsCount[5] >= $i ? ' visible-xs-landscape' : '';
			$classesToAdd .= $dummyVendorsCount[6] >= $i ? ' visible-md visible-sm' : '';
			$classesToAdd .= $dummyVendorsCount[7] >= $i ? ' visible-sm-landscape' : '';
			$classesToAdd .= $dummyVendorsCount[8] >= $i ? ' visible-lg' : '';

			$clonedDummy = clone($dummyVendor);
			$clonedDummy->setClasses($classesToAdd);

			$collection->addItem($clonedDummy);
		}

	}
	
	/**
	 * @param ZolagoOs_OmniChannel_Model_Vendor $vendor
	 * @return string
	 */
	public function getVendorName(ZolagoOs_OmniChannel_Model_Vendor $vendor) {
		return $vendor->getVendorName();
	}
	
	/**
	 * @param ZolagoOs_OmniChannel_Model_Vendor $vendor
	 * @return string | null
	 */
	public function getVendorMarkUrl(ZolagoOs_OmniChannel_Model_Vendor $vendor) {
		return $vendor->getFileUrl('logo');
	}
	
	/**
	 * @param ZolagoOs_OmniChannel_Model_Vendor $vendor
	 * @return string | null
	 */
	public function getVendorResizedLogoUrl(ZolagoOs_OmniChannel_Model_Vendor $vendor, 
			$width=130, $height=74) {
        /* @var $zolagodropship Zolago_Dropship_Helper_Data */
		$zolagodropship = Mage::helper("zolagodropship");

		return $zolagodropship->getVendorLogoResizedUrl($vendor, $width, $height);
	}
	
	/**
	 * @param ZolagoOs_OmniChannel_Model_Vendor $vendor
	 * @return string
	 */
	public function getVendorBaseUrl(ZolagoOs_OmniChannel_Model_Vendor $vendor) {
		return Mage::helper("umicrosite")->getVendorBaseUrl($vendor);
	}

	
} 