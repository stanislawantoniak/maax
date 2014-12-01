<?php
class Zolago_Solrsearch_Model_Catalog_Product_List extends Varien_Object{
	
	const MODE_CATEGORY = 1;
    const MODE_SEARCH = 2;
	
	const DEFAULT_DIR = "desc";
	const DEFAULT_ORDER = "wishlist_count";
	const DEFAULT_LIMIT = 24;
	
	const DEFAULT_START = 0;
	const DEFAULT_PAGE = 1;

    const DEFAULT_APPEND_WHEN_SCROLL = 28;
    const DEFAULT_LOAD_MORE_OFFSET = 100;
    const DEFAULT_PIXELS_BEFORE_APPEND = 2500;
	
	
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
			//Mage::log("Prepare collection start");
			$collection = Mage::getModel("zolagosolrsearch/catalog_product_collection");
			/* @var $collection Zolago_Solrsearch_Model_Catalog_Product_Collection */
			$collection->setFlag("store_id", Mage::app()->getStore()->getId());
			$data = $this->getSolrData();
			if (is_array($data)) {
    			$collection->setSolrData($this->getSolrData());
			}
			$collection->setCurrentCategory($this->getCurrentCategory());
			$collection->load();
			$this->setData("collection", $collection);
			//Mage::log("Prepare collection stop");
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
	 * Query param: sort
	 * @return int
	 */
	public function getCurrentOrder() {
		return Mage::app()->getRequest()->getParam("sort", $this->getDefaultOrder());
	}
	
	
	/**
	 * Query param: start
	 * Get colleciton start row (offset) - ignored if page is set
	 * @return int
	 */
	public function getCurrentStart() {
		$request = Mage::app()->getRequest();
		
		// Page is set so start is callucled by bage
		if((int)$request->getParam("page")){
			return ($this->getCurrentPage()-1) * $this->getCurrentLimit();
		}
		
		$start = $request->getParam("start");
		
		if((int)$start>=0){
			return (int)$start;
		}
		
		return self::DEFAULT_START;
	}
	
	/**
	 * Query param: page
	 * @return int
	 */
	public function getCurrentPage() {
		$request = Mage::app()->getRequest();
		// Ajax request or Google bot - serve paged content
		if($request->isAjax() || $this->isGoogleBot()){
			$page = (int)$request->getParam("page");
			if($page>0){
				return $page;
			}
		}
		// Normal request by human - only first page served
		return self::DEFAULT_PAGE;
	}
	
	/**
	 * Query param: rows
	 * @return int
	 */
	public function getCurrentLimit() {
		$queryLimit = (int)Mage::app()->getRequest()->getParam("rows");
		if($queryLimit>0){
			return $queryLimit;
		}
		return $this->getDefaultLimit();
	}
	
	/**
	 * Query param: dir
	 * @return string
	 */
	public function getCurrentDir() {
		$dir = Mage::app()->getRequest()->getParam("dir");
		return in_array($dir, array("asc", "desc")) ? $dir : $this->getDefaultDir();
	}
	
	/**
	 * @return int
	 */
	public function getDefaultLimit()
    {
        $limit = (int) Mage::getStoreConfig("zolagomodago_catalog/zolagomodago_cataloglisting/load_on_start"
            , Mage::app()->getStore());

        if ($limit === 0) {
            $limit = self::DEFAULT_LIMIT;
        }

		return $limit;
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
	
	/**
	 * get current url path placed in 
	 * @return string
	 */
	public function getUrlRoute() {
		if(!$this->hasData("url_route")){
			if($this->isCategoryMode()){
				$path = "catalog/category/view";
			}else{
				$path = "search/index/index";
			}
			$this->setData("url_route", $path);
		}
		return $this->getData("url_route");
	}
	
	/**
	 * get current url path placed in 
	 * @return string
	 */
	public function getUrlPathForCategory() {
		if(!$this->hasData("url_path_for_category")){
			if($this->isCategoryMode()){
				$path = $this->getCurrentCategory()->getUrlPath();
			}elseif($this->isSearchMode()){
//                echo ' 123 ';
//                print_r($this->getCurrentCategory()->getUrlPath());
                $path = false;
			} else {
                $path = false;
            }
			$this->setData("url_path_for_category", $path);
		}
		return $this->getData("url_path_for_category");
	}
	
	/**
	 * @return bool
	 */
	public function isCategoryMode() {
		return $this->getMode() == self::MODE_CATEGORY;
	}
	
	/**
	 * @return bool
	 */
	public function isSearchMode() {
		return $this->getMode() == self::MODE_SEARCH;
	}
}