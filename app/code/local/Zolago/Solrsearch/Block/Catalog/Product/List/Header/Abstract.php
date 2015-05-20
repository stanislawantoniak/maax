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
        $currentCategory = $this->getListModel()->getCurrentCategory();
        $rewriteData = Mage::helper("ghrewrite")->getCategoryRewriteData();
        if (!empty($rewriteData)) {
            if (isset($rewriteData['category_name']) && !empty($rewriteData['category_name'])) {
                $currentCategory->setLongName($rewriteData['category_name']);
            }
        }
		return $currentCategory;
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