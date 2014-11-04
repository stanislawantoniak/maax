<?php
/**
 * Description of Title
 */
class Zolago_Solrsearch_Block_Catalog_Product_List_Header_Abstract
	extends SolrBridge_Solrsearch_Block_Result_Title {
	
	/**
	 * @return Mage_Catalog_Model_Category
	 */
	public function getCategory() {
		return $this->getListModel()->getCurrentCategory();
	}
	
	/**
	 * @return array
	 */
	public function getSolrData(){
		return $this->getListModel()->getSolrData();
	}
	
	/**
	 * @return Zolago_Solrsearch_Model_Catalog_Product_List
	 */
	public function getListModel() {
		return Mage::getSingleton('zolagosolrsearch/catalog_product_list');
	}
	
}