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
        $attributeSet = $this->getRequest()->getParam('attribute_set');

        $staticFilters = array();
        if(!empty($attributeSet)){
            $array = Mage::getResourceSingleton('zolagocatalog/vendor_mass')
                ->getStaticFiltersForVendor(
                    $this->getVendor(),
                    $this->getCurrentAttributeSetId()
                );

            $arrayDropdown = Mage::getResourceSingleton('zolagocatalog/vendor_mass')
                ->getStaticDropdownFiltersForVendorProductAssoc(
                    $this->getVendor(),
                    $this->getCurrentAttributeSetId(),
                    $this->getStore()->getId()
                );

            $staticFilters = array_merge($array, $arrayDropdown);
            $staticFilters = $this->_sortStaticFiltersbyColumns($staticFilters, array('groupOrder' => SORT_ASC, 'sortOrder' => SORT_ASC));
        }

        return $staticFilters;
    }

    /**
     * Sort MultiDimensional Array by Multiple Columns
     *
     * @param array $array
     * @param array $columns
     *
     * @return array $result Sorted Array
     */
    protected function _sortStaticFiltersbyColumns($array, $columns)
    {
        $columnArray = array();
        foreach ($columns as $column => $order) {
            $columnArray[$column] = array();
            foreach ($array as $key => $row) {
                $columnArray[$column]['_'.$key] = strtolower($row[$column]);
            }
        }

        $eval = 'array_multisort(';
        foreach ($columns as $column => $order) {
            $eval .= '$columnArray[\''.$column.'\'],'.$order.',';
        }

        $eval = substr($eval,0,-1).');';
        eval($eval);
        $result = array();

        foreach ($columnArray as $column => $arr) {
            foreach ($arr as $key => $value) {
                $key = substr($key,1);
                if (!isset($result[$key])) {
                    $result[$key] = $array[$key];
                }
                $result[$key][$column] = $array[$key][$column];
            }
        }

        return $result;
    }

    public function getOptionLabelbyId($attributeId,$labelId, $storeId = 0)
    {
        $optionValue = Mage::getResourceSingleton('zolagocatalog/vendor_mass')
                       ->getOptionLabelbyId(
                           $attributeId,
                           $labelId,
                           $storeId
                       );

        return $optionValue;
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
        if($this->getParentBlock()) {
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
        if (!$value) {
            $value = Mage::helper("zolagocatalog")->__('--- no data ---');
        }
        return $this->escapeHtml($value);
    }

    /**
     * label store assigned to vendor
     */
    protected function _getLabelStore() {
        if (!$this->getData('label_store')) {
            $store = null;
            if($this->getVendor() && $this->getVendor()->getLabelStore()) {
                Mage::helper('udropship')->loadCustomData($this->getVendor());
                $store = Mage::app()->getStore($this->getVendor()->getLabelStore());
            }
            if(!$store || !$store->getId()) {
                $store = $this->getStore();
            }
            $this->setData("label_store", $store);
        }
        return $this->getData('label_store');

    }
    public function getAttributeLabel($code, $store) {
        $storeLabel = false;
        $attribute = Mage::getModel('catalog/resource_eav_attribute')
                     ->loadByCode(Mage_Catalog_Model_Product::ENTITY, $code);
        if ($attribute && $attribute->getId()) {
            $storeLabel = $attribute->getStoreLabel($this->_getLabelStore());
        }

        return $storeLabel;
    }

    public function isFilterActive($activeFilters, $attributeId, $displayValue, $filterValue)
    {
        $active = false;
        if (($activeFilters
                && array_key_exists($attributeId, $activeFilters)
                && $this->escapeHtml($activeFilters[$attributeId]) == $displayValue)
                || ($activeFilters
                    && array_key_exists($attributeId, $activeFilters)
                    && $activeFilters[$attributeId] == $filterValue)):
                $active = true;
        endif;

        return $active;
    }
}