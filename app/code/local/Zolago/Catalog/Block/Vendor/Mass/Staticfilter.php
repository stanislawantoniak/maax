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
	
	public function getStaticFilterLabel($singleFilter)
	{
		$firstFilter = current($singleFilter);
		$filterLabel = $this->getAttributeLabel($firstFilter['code'], $this->getStore());
		$labelsCount = array();
		
		$specialLabels = array();
		foreach ($singleFilter as $value):
			$startLabel = strpos($value['value'], Zolago_Catalog_Helper_Data::SPECIAL_LABELS_OLD_DELIMITER);
			if ($startLabel !== false) {
				$properLabel = trim(substr($value['value'], 0, $startLabel));
				$specialLabels[$properLabel] = $properLabel;
				if (array_key_exists($properLabel, $labelsCount)) {
					$labelsCount[$properLabel] = $labelsCount[$properLabel]+1;
				} else {
					$labelsCount[$properLabel] = 1;
				}
			}			
		endforeach;

		if ($specialLabels) {
			$filterLabel = implode(Zolago_Catalog_Helper_Data::SPECIAL_LABELS_NEW_DELIMITER, array_keys($specialLabels));
		}
		return array($filterLabel, $labelsCount);
	}
	
	public function updateStaticFilterValues(&$singleFilter, $labelsCount)
	{
		$update = false;
		foreach ($singleFilter as $filtereKey => $filterValue) {
			if (empty($filterValue['value'])) {
				unset($singleFilter[$filtereKey]);
			}
		}

		if (count($singleFilter) == array_shift($labelsCount)) {
			$update = true;
		}
		return $update;
	}
	
	public function getUpdatedFilterValues($value, $filterLabel, $update) {
		if ($update) {
			$value = trim(substr($value, strlen($filterLabel)+1));
		}
		return $this->escapeHtml($value);
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