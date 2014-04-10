<?php
class Zolago_Catalog_Block_Vendor_Mass_Staticfilter extends Mage_Core_Block_Template
{
	public function getStaticFilters() {
		$staticFiltersCollection = array();
		$rawStaticFiltersCollection = $this->getRawStaticFilters();
		foreach ($rawStaticFiltersCollection as $staticFilter) {
			$staticFiltersCollection[$staticFilter['attribute_id']][] = $staticFilter;
		}
		
		return $staticFiltersCollection;
	}
	
	public function getRawStaticFilters() {
		$array = Mage::getResourceSingleton('zolagocatalog/vendor_mass')
			->getStaticFiltersForVendor(
				$this->getVendor(),
				$this->getCurrentAttributeSetId()
		);
		return $array;
	}
	
	public function getCurrentAttributeSetId() {
		return $this->getParentBlock()->getCurrentAttributeSetId();
	}	

	public function getChangeUrl() {
		return $this->getUrl("*/*/*");
	}	
	
	public function getVendor() {
		return Mage::getModel("udropship/session")->getVendor();
	}

	public function getCurrentStaticFilterValues() {
		if(!$this->getData("current_static_filter_value")) {
			$staticFilters			= Mage::app()->getRequest()->getParam("staticFilters", 0);
			$staticFiltersValues	= false;
			
			for ($i = 1; $i <= $staticFilters; $i++) {
				if (Mage::app()->getRequest()->getParam("staticFilterId-".$i) && Mage::app()->getRequest()->getParam("staticFilterValue-".$i)) {
					$staticFiltersValues[Mage::app()->getRequest()->getParam("staticFilterId-".$i)] = Mage::app()->getRequest()->getParam("staticFilterValue-".$i);
				}
			}
			
			$this->setData("current_static_filter_value",
				$staticFiltersValues
			);
		}
		return $this->getData("current_static_filter_value");
	}
	
	/**
	 * @return Mage_Core_Model_Store
	 */
	public function getStore() {
		if($this->getParentBlock()){
			return $this->getParentBlock()->getCurrentStore();
		}
		return Mage::app()->getStore(
			Mage::app()->getRequest()->getParam("store", 0)
		);
	}
	
	public function getAttributeLabel($code, $store) {
		$storeLabel = false;
		$attribute = Mage::getModel('catalog/resource_eav_attribute')
						->loadByCode(Mage_Catalog_Model_Product::ENTITY, $code);
		if ($attribute && $attribute->getId()) {
			$storeLabel = $attribute->getStoreLabel($store->getId());
		}
		
		return $storeLabel;
	}
}