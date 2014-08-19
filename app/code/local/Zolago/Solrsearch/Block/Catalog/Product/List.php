<?php
class Zolago_Solrsearch_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List {
	
	/**
	 * @return Zolago_Solrsearch_Model_Catalog_Product_Collection
	 */
	public function getCollection() {
		return Mage::getSingleton('zolagosolrsearch/catalog_product_list')->getCollection();
	}

    public function getLoadLimit()
    {
        return Zolago_Solrsearch_Model_Catalog_Product_List::DEFAULT_LIMIT;
    }
	
//	protected function _toHtml() {
//		Mage::log("Before hmtl list");
//		$ret = parent::_toHtml();
//		Mage::log("After html list");
//		return $ret;
//	}
//	
}
