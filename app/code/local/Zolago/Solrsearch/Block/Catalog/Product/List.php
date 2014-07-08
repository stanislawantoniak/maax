<?php
class Zolago_Solrsearch_Block_Catalog_Product_List extends Mage_Core_Block_Template {
	
	/**
	 * @return Zolago_Solrsearch_Model_Catalog_Product_Collection
	 */
	public function getCollection() {
		if(!$this->getData("collection")){
			$collection = Mage::getModel("zolagosolrsearch/catalog_product_collection");
			/* @var $collection Zolago_Solrsearch_Model_Catalog_Product_Collection */
			$collection->setFlag("store_id", Mage::app()->getStore()->getId());
			$collection->setSolrData($this->getSolrData());
			$collection->load();
			$this->setData("collection", $collection);
		}
		return $this->getData("collection");
	}
	
	public function getSolrData() {
		$solrData = Mage::registry(Zolago_Solrsearch_Model_Solr::REGISTER_KEY);

    	if (!$solrData) {
    		$queryText = Mage::helper('solrsearch')->getParam('q');
    		Mage::getModel('solrsearch/solr')->queryRegister($queryText);
    	}
		
		return Mage::registry(Zolago_Solrsearch_Model_Solr::REGISTER_KEY);
  
	}

	
	
}
