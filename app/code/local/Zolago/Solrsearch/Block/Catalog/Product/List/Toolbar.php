<?php
class Zolago_Solrsearch_Block_Catalog_Product_List_Toolbar extends Mage_Core_Block_Template {
	
	/**
	 * @return int
	 */
	public function getNumFound() {
		return 1;
	}
	
	/**
	 * 
	 */
	public function getSortOptions() {
		return array();
	}
	
	public function getListBlock() {
		
	}
}
