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
}