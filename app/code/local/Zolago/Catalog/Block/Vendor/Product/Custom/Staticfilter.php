<?php
class Zolago_Catalog_Block_Vendor_Product_Custom_Staticfilter
	extends Zolago_Catalog_Block_Vendor_Mass_Staticfilter
{

    public function getRawStaticFilters() {
        $attributeSet = $this->getCurrentAttributeSetId();

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
            $staticFilters = $this->_sortStaticFiltersbyColumns(
					$staticFilters, 
					array('groupOrder' => SORT_ASC, 'sortOrder' => SORT_ASC)
			);
        }

        return $staticFilters;
    }
	
	/**
	 * @return int
	 */
	public function getCurrentAttributeSetId() {
		return $this->getAttributeSetId();
	}
	
	/**
	 * @return int
	 */
	public function getAttributeSetId() {
		return $this->getGridModel()->getAttributeSet()->getId();
	}
	
	/**
	 * @return Mage_Core_Model_Store Description
	 */
	public function getStore() {
		return $this->getGridModel()->getStore();
	}
	
	/**
	 * @return Zolago_Catalog_Model_Vendor_Product_Grid
	 */
	public function getGridModel() {
		return Mage::getSingleton('zolagocatalog/vendor_product_grid');
	}

}