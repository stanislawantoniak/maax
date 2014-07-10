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
	/**
	 * @return array
	 */
	public function getSortOptions() {
		$options = array();
		
		$options[] = array(
			'value' => 'wishlist_count',
			'dir'   => 'desc',
			'label' =>  Mage::helper("zolagosolrsearch")->__("Most popular first")
		);
		
		$options[] = array(
			'value' => 'is_new',
			'dir'   => 'desc',
			'label' =>  Mage::helper("zolagosolrsearch")->__("New products first")
		);
		
		$options[] = array(
			'value' => 'price',
			'dir'   => 'desc',
			'label' =>  Mage::helper("zolagosolrsearch")->__("Most expensive first")
		);
		
		$options[] = array(
			'value' => 'price',
			'dir'   => 'asc',
			'label' =>  Mage::helper("zolagosolrsearch")->__("Least expensive first")
		);
		
		$options[] = array(
			'value' => 'product_rating',
			'dir'   => 'desc',
			'label' =>  Mage::helper("zolagosolrsearch")->__("Best rated")
		);
		return $options;
	}
	
	public function getCurrentOrder() {
		return Mage::app()->getRequest()->getParam("order", $this->getDefaultOrder());
	}
	
	public function getCurrentPage() {
		return (int)Mage::app()->getRequest()->getParam("page", 1);
	}
	
	public function getCurrentDir() {
		return Mage::app()->getRequest()->getParam("dir", $this->getDefaultDir());
	}
	
	public function getDefaultDir() {
		return "asc";
	}
	
	public function getDefaultOrder() {
		return "wishlist_count";
	}
}