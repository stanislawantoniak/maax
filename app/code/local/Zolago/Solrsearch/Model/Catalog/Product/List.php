<?php
class Zolago_Solrsearch_Model_Catalog_Product_List extends Varien_Object {

    const MODE_CATEGORY = 1;
    const MODE_SEARCH = 2;

	const DEFAULT_DIR = "desc";
	const DEFAULT_SEARCH_DIR = "desc";
	const DEFAULT_ORDER = "popularity";
	const DEFAULT_SEARCH_ORDER = "relevance";
	
    const DEFAULT_LIMIT = 100;

    const DEFAULT_START = 0;
    const DEFAULT_PAGE = 1;

    const DEFAULT_APPEND_WHEN_SCROLL = 28; //TODO remove
    const DEFAULT_LOAD_MORE_OFFSET = 100; //TODO remove
    const DEFAULT_PIXELS_BEFORE_APPEND = 2500; //TODO remove

    protected $_actualMode;
    
    /**
     * @return string
     */
    public function getCurrentUrlPath() {
        if($this->isSearchMode()) {
            return "search";
        }
        return "catalog/category/view";
    }

    /**
     * @return int
     */
    public function getMode() {
        if (is_null($this->_actualMode)) {
            $queryText = Mage::helper('solrsearch')->getParam('q');
            if($this->getCurrentCategory() && !Mage::registry('current_product') && !$queryText) {
                $mode = self::MODE_CATEGORY;
            } else {
                $mode = self::MODE_SEARCH;
            }
            $this->_actualMode = $mode;
        }
        return $this->_actualMode;
    }

    /**
     * @return Zolago_Solrsearch_Model_Catalog_Product_Collection
     */
    public function getCollection() {
        if(!$this->getData("collection")) {
            $collection = Mage::getModel("zolagosolrsearch/catalog_product_collection");
            /* @var $collection Zolago_Solrsearch_Model_Catalog_Product_Collection */
            $collection->setFlag("store_id", Mage::app()->getStore()->getId());
            $data = $this->getSolrData();

            if (is_array($data)) {
                $collection->setSolrData($this->getSolrData());
                $fq = Mage::helper('solrsearch')->getParam('fq');

                if ($this->getMode() == self::MODE_CATEGORY && empty($fq)) {
                    $numFound = isset($data["response"]["numFound"]) ? (int)$data["response"]["numFound"] : 0;

                    if (empty($numFound)) {
                        //Clear cache solr_products_count (jako klient nie chcę widzieć kategorii w których nie ma produktów)
                        $category = $this->getCurrentCategory();
                        $vid = 0;
                        if ($vendorContext = Mage::helper("umicrosite")->getCurrentVendor()) {
                            $vid = $vendorContext->getId();
                        }
                        $cacheKey = sprintf("SOLR_PRODUCTS_COUNT_%d_%d_%d", $category->getId(), $vid, Mage::app()->getStore()->getId());
                        Mage::getModel("zolagocatalog/category")->getCategoryCacheHelper()->_saveInCache($cacheKey, $numFound);
                    }

                }
            }
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
        if(Mage::registry('current_category') instanceof Mage_Catalog_Model_Category) {
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
		// Add relavance to search
		if($this->isSearchMode()){
			$options[] = array(
				'sort' => 'relevance',
				'dir'   => 'desc',
				'label' =>  Mage::helper("zolagosolrsearch")->__("Best match")
			);
		}
		
		$options[] = array(
			'sort' => 'price',
			'dir'   => 'asc',
			'label' =>  Mage::helper("zolagosolrsearch")->__("Price ascendend")
		);
		
		$options[] = array(
			'sort' => 'price',
			'dir'   => 'desc',
			'label' =>  Mage::helper("zolagosolrsearch")->__("Price descendent")
		);
		
		$options[] = array(
			'sort' => 'popularity',
			'dir'   => 'desc',
			'label' =>  Mage::helper("zolagosolrsearch")->__("Most popular first")
		);
		
		$options[] = array(
			'sort' => 'product_rating',
			'dir'   => 'desc',
			'label' =>  Mage::helper("zolagosolrsearch")->__("Best rated")
		);
		
		$options[] = array(
			'sort' => 'created_at',
			'dir'   => 'desc',
			'label' =>  Mage::helper("zolagosolrsearch")->__("New products")
		);

		$options[] = array(
			'sort' => 'delta_price',
			'dir'   => 'desc',
			'label' =>  Mage::helper("zolagosolrsearch")->__("Best deals")
		);
		
		return $options;
	}

    /**
     * Query param: sort
     * @return int
     */
    public function getCurrentOrder() {
	    $sort = Mage::app()->getRequest()->getParam("sort");
        return $sort ? $sort : $this->getDefaultOrder();
    }


    /**
     * Query param: start
     * Get collection start row (offset) - ignored if page is set
     * @return int
     */
    public function getCurrentStart()
    {
        $request = Mage::app()->getRequest();

        $start = (int)$request->getParam("start", 0);

        $currentStart = $start - 1;

        if ($currentStart >= 0) {
            return $currentStart;
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
        if($request->isAjax() || $this->isGoogleBot()) {
            $page = (int)$request->getParam("page");
            if($page>0) {
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
	    $limit = Mage::helper("zolagocatalog/listing_pagination")->productsCountPerPage() + 8; // 8 more because of listing fadeout

		    if ($limit === 0) {
			    $limit = self::DEFAULT_LIMIT;
		    }

        return $limit;
    }

	/**
	 * @return string
	 */
	public function getDefaultDir() {
		if($this->isSearchMode()){
			return self::DEFAULT_SEARCH_DIR;
		}
		return self::DEFAULT_DIR;
	}
	
	/**
	 * @return string
	 */
	public function getDefaultOrder() {
		if($this->isSearchMode()){
			return self::DEFAULT_SEARCH_ORDER;
		}
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
        if(!$this->hasData("url_route")) {
            if($this->isCategoryMode()) {
                $path = "catalog/category/view";
            } else {
                $path = "search";
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
        if(!$this->hasData("url_path_for_category")) {
            if($this->isCategoryMode()) {
                $path = $this->getCurrentCategory()->getUrlPath();
            }
            elseif($this->isSearchMode()) {
                $path = $this->getUrlRoute() . '/';
            }
            else {
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

    /**
     * Products count found bu solr search
     * @return int
     */
    public function getProductsFound()
    {
        $data = Mage::registry(Zolago_Solrsearch_Model_Solr::REGISTER_KEY);
        $numFound = (empty($data['response']['numFound']) ? 0 : (int)$data['response']['numFound']);

        return $numFound;
    }

    /**
     * number of pages
     * @return bool
     */
    public function getPageCounter()
    {
        $numFound = $this->getProductsFound();
        return ceil($numFound / $this->getDefaultLimit());
    }
}