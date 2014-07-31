<?php
class Zolago_Solrsearch_Model_Catalog_Product_List extends Varien_Object{
	
	const MODE_CATEGORY = 1;
    const MODE_SEARCH = 2;
	
	const DEFAULT_DIR = "desc";
	const DEFAULT_ORDER = "wishlist_count";
	const DEFAULT_LIMIT = 40;
	
	
	/**
	 * @return int
	 */
    public function getMode() {
		$queryText = Mage::helper('solrsearch')->getParam('q');
        if($this->getCurrentCategory() && !Mage::registry('current_product') && !$queryText) {
            return self::MODE_CATEGORY;
        }
        return self::MODE_SEARCH;
    }
	
	/**
	 * @return Zolago_Solrsearch_Model_Catalog_Product_Collection
	 */
	public function getCollection() {
		if(!$this->getData("collection")){
			Mage::log("Prepare collection start");
			$collection = Mage::getModel("zolagosolrsearch/catalog_product_collection");
			/* @var $collection Zolago_Solrsearch_Model_Catalog_Product_Collection */
			$collection->setFlag("store_id", Mage::app()->getStore()->getId());
			$collection->setSolrData($this->getSolrData());
			$collection->setCurrentCategory($this->getCurrentCategory());
			$collection->load();
			$this->setData("collection", $collection);
			Mage::log("Prepare collection stop");
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
    		$queryText = $this->getQueryText();
    		Mage::getModel('solrsearch/solr')->queryRegister($queryText);
    	}
		
		return Mage::registry(Zolago_Solrsearch_Model_Solr::REGISTER_KEY);
  
	}
	
	/**
	 * @return string
	 */
	public function getQueryText() {
		return Mage::helper('solrsearch')->getParam('q');
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
	
	/**
	 * @return int
	 */
	public function getCurrentOrder() {
		return Mage::app()->getRequest()->getParam("order", $this->getDefaultOrder());
	}
	
	/**
	 * @return int
	 */
	public function getCurrentPage() {
		$request = Mage::app()->getRequest();
		// Ajax request or Google bot - serve paged content
		if($request->isAjax() || $this->isGoogleBot()){
			return (int)$request->getParam("page", 1);
		}
		// Normal request by human - only first page served
		return 1;
	}
	
	/**
	 * @return int
	 */
	public function getCurrentLimit() {
		return $this->getDefaultLimit();
	}
	
	/**
	 * @return string
	 */
	public function getCurrentDir() {
		return Mage::app()->getRequest()->getParam("dir", $this->getDefaultDir());
	}
	
	/**
	 * @return int
	 */
	public function getDefaultLimit() {
		return self::DEFAULT_LIMIT;
	}
	
	/**
	 * @return string
	 */
	public function getDefaultDir() {
		return self::DEFAULT_DIR;
	}
	
	/**
	 * @return string
	 */
	public function getDefaultOrder() {
		return self::DEFAULT_ORDER;
	}
	
	/**
	 * @return bool
	 */
	public function isGoogleBot() {
		return Mage::helper("zolagocommon")->isGoogleBot();
	}
}