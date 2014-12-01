<?php

class Zolago_Catalog_Block_Vendor_Product_Custom_Columns 
	extends Mage_Core_Block_Template {
	
	/**
	 * @param array $column
	 * @return boolean
	 */
	public function isChecked(array $column) {
		return !$this->getGridModel()->isDenyColumn($column['index']); 
	}
	
	/**
	 * @param array $column
	 * @return boolean
	 */
	public function getHtmlId(array $column) {
		return "hide-" . $column['index'];
	}
	
	
	/**
	 * @return array
	 */
	public function getAllColumns() {
		return $this->getGridModel()->getAllColumns();
	}
	
	/**
	 * @return Zolago_Catalog_Model_Vendor_Product_Grid
	 */
	public function getGridModel() {
		return Mage::getSingleton('zolagocatalog/vendor_product_grid');
	}
}