<?php
class Zolago_Solrsearch_Model_Catalog_Product_List extends Varien_Object{
	/**
	 * @return Zolago_Solrsearch_Model_Catalog_Product_Collection
	 */
	public function getCollection() {
		if(!$this->getData("collection")){
			$collection = Mage::getModel("zolagosolrsearch/catalog_product_collection");
			/* @var $collection Zolago_Solrsearch_Model_Catalog_Product_Collection */
			$collection->setFlag("store_id", Mage::app()->getStore()->getId());
			$collection->setSolrData($this->getSolrData());
			$collection->setCurrentCategory($this->getCurrentCategory());
			$collection->load();
			$this->setData("collection", $collection);
		}
		return $this->getData("collection");
	}
	
	/**
	 * @todo more flexible
	 * @return Mage_Catalog_Model_Category
	 */
	public function getCurrentCategory() {
		if(Mage::registry('current_category') instanceof Mage_Catalog_Model_Category){
			return Mage::registry('current_category');
		}
		return Mage::helper("zolagodropshipmicrosite")->getVendorRootCategoryObject();
	}
	
	/**
	 * @todo more flexible
	 * @return array
	 */
	public function getSolrData() {
		$solrData = Mage::registry(Zolago_Solrsearch_Model_Solr::REGISTER_KEY);

    	if (!$solrData) {
    		$queryText = Mage::helper('solrsearch')->getParam('q');
    		Mage::getModel('solrsearch/solr')->queryRegister($queryText);
    	}
		
		return Mage::registry(Zolago_Solrsearch_Model_Solr::REGISTER_KEY);
  
	}
}