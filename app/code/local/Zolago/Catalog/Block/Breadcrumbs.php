<?php
class Zolago_Catalog_Block_Breadcrumbs extends Mage_Catalog_Block_Breadcrumbs
{
    protected $_vendor;
    protected $_breadcrumbBlock;
    protected $_path;
    protected $_rootId;

    /**
     * get product
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct() {
        if (($product = Mage::registry('current_product'))
                       && (Mage::registry('current_product') instanceof Mage_Catalog_Model_Product)) {
            return $product;
        } else {
            return 0;
        }
    }
    /**
     * return breadcrumb block
     * @return
     */

    protected function _toHtml() {
        return $this->getLayout()->getBlock('breadcrumbs')->toHtml();
    }

    /**
     * prepare breadcrumb path
     * @return array
     */
    protected function _getPath() {
        if (is_null($this->_path)) {
            /* @var $catalogHelper Mage_Catalog_Helper_Data */
            $catalogHelper = Mage::helper('catalog');
            $category = $catalogHelper->getCategory();

            $refererUrl = $this->getRequest()->getServer("HTTP_REFERER");
            $params = explode("&", $refererUrl);

            if (
                !$category
                || $category->getId() == $this->_getRootCategoryId()
                || (Mage::registry('current_product') && (int)strpos($refererUrl,"search") > 0 && !in_array("scat=".$category->getId(), $params))
            ) {
                $category = $this->_getDefaultCategory(
					$this->_getProduct(),
					$this->_getRootCategoryId()
				);
            }
            if ($category) {
                $path = $this->_preparePath($category);
            } else {
                $path = array();
            }
            if ($product = $this->_getProduct()) {
                $path['product'] = array('label'=>$product->getName());
            }

            $this->_path = $path;
        }

        return $this->_path;
    }
    /**
     * get type name by vendor type
     * @param int $vendorType;
     * @return string
     */
    protected function _getVendorTypeName($vendorType) {
        $out = '';
        $helper = Mage::helper('catalog');
        switch ($vendorType) {
        case Zolago_Dropship_Model_Vendor::VENDOR_TYPE_STANDARD:
            $out = $helper->__('Seller');
            break;
        case Zolago_Dropship_Model_Vendor::VENDOR_TYPE_BRANDSHOP:
            $out = ' '.$helper->__('Shop');
            break;
        }
        return $out;
    }

    /**
     * get actual vendor
     * @return
     */
    protected function _getVendor() {
        if (is_null($this->_vendor)) {
            $this->_vendor = 0;
            // Add vendor
            $vendor = Mage::helper('umicrosite')->getCurrentVendor();
            if ($vendor && $vendor->getId()) {
                $this->_vendor = $vendor;
            }
        }
        return $this->_vendor;
    }

 

	/**
	 * 
	 * @return boolean
	 */
	protected function _isSearchContext(){
		$request = $this->getRequest();
		return (
			$request->getModuleName()=="search" && 
			$request->getControllerName()=="index" && 
			$request->getActionName()=="index"
		);
	}
	
	/**
	 * @return string | null
	 */
	protected function _getQuery() {
		return Mage::helper("solrsearch")->getParam("q");
	}
	
	/**
	 * @param array $params
	 * @return string
	 */
	public function getSearchLink(array $params = array()) {
		return $this->getUrl("search", $params);
	}
	
	/**
	 * 
	 * @param type $category
	 * @param type $parentCategory
	 * @param type $parentId
	 * @return string
	 */
	protected function _prepareCategoryLink($category, $parentCategory, $parentId) {
		if($this->_isSearchContext()){
			return $this->getSearchLink(array(
				"_query"=>array(
					"q"=>$this->_getQuery(),
					"scat"=>$parentId
				)
			));
		}
		if($category->getId() != $parentId || $this->_getProduct()){
			return $parentCategory->getUrl();
		};
		return '';
	}
	 
    /**
     * preparing path
     * @param
     * @return array
     */
    protected function _preparePath($category) {
        $path = array();
        $rootId = $this->_getRootCategoryId();

        /*  @var $lpBlock Zolago_Catalog_Block_Campaign_LandingPage */
        $lpBlock = Mage::getBlockSingleton('zolagocatalog/campaign_landingPage');
        $lpData = $lpBlock->getData('campaign_landing_page');
        $lpData = (array)$lpData;

        /* @var $category Mage_Catalog_Model_Category */
        if($category->getId() && ($parents = $category->getParentCategories())) {
            $pathIds = array_reverse($category->getPathIds());
            // Remove root category
            array_pop($pathIds);
            foreach($pathIds as $parentId) {

                if ($parentId == $rootId) {
                    break; // we are in root
                }
                if(isset($parents[$parentId]) && $parents[$parentId]
                        instanceof Mage_Catalog_Model_Category) {

                    $parentCategory = $parents[$parentId];
                    $categoryName = $parentCategory->getName();
                    $categoryLongName = $parentCategory->getLongName();
                    $link = $this->_prepareCategoryLink($category, $parentCategory, $parentId);

                    if(!empty($lpData)){
                        if(isset($lpData["campaign"]) && $parentCategory->getId() == $lpData["campaign"]){
                            $categoryName = $lpData["name_customer"];
                            $categoryLongName = $lpData["name_customer"];
                            $link = $lpData["url"];
                        }
                    }

                    array_unshift($path, array(
						"name"      => "category" . $parentCategory->getId(),
                        "id"        => $parentCategory->getId(),
						"label"     => $categoryName,
						"link"      => $link,
                        "data-link" => $parentCategory->getUrl(),
                        'categorylongname' => $categoryLongName
					));
                }
            }
        }
        return $path;
    }

    /**
     * breadcrumb for product
	 * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _getDefaultCategory($product, $rootId) {
		return Mage::helper("zolagosolrsearch")->getDefaultCategory($product, $rootId);
    }
	   
    /**
     * id of root category (depends from website and vendor)
     * @return int
     */
     protected function _getRootCategoryId() {
        return Mage::helper("zolagosolrsearch")->getRootCategoryId();
     }
	
    /**
     * breadcrumb for listing
     * @return array
     */
    protected function _prepareListingBreadcrumb() {
        $vendor = $this->_getVendor();
        $breadcrumbsBlock = $this->_getBlock();
        /*  @var $lpBlock Zolago_Catalog_Block_Campaign_LandingPage */
        /*$lpBlock = Mage::getBlockSingleton('zolagocatalog/campaign_landingPage');
        $lpData = $lpBlock->getData('campaign_landing_page');
        $lpData = (array)$lpData;*/

        if($vendor) {
            $breadcrumbsBlock->addCrumb('home', array(
				'label'=>Mage::helper('catalog')->__('Mall'),
				'title'=>Mage::helper('catalog')->__('Go to Mall'),
				'link'=>Mage::helper("zolagodropshipmicrosite")->getBaseUrl()
			));

            $breadcrumbsBlock->addCrumb('vendor', array(
				'label'=>Mage::helper('catalog')->__($vendor->getVendorName()),
				'title'=>Mage::helper('catalog')->__($vendor->getVendorName()),
				'link'=>Mage::getBaseUrl()
			));
        } else {
            $breadcrumbsBlock->addCrumb('home', array(
				'label'=>Mage::helper('catalog')->__('Home'),
				'title'=>Mage::helper('catalog')->__('Go to Home Page'),
				'link'=>Mage::getBaseUrl()
			));
        }
		
		if($this->_isSearchContext()){
			$breadcrumbsBlock->addCrumb('search', array(
				'label'=>Mage::helper('catalog')->__('Search: %s', $this->escapeHtml($this->_getQuery())),
				'title'=>Mage::helper('catalog')->__('Search: %s', $this->escapeHtml($this->_getQuery())),
				'link'=>$this->getSearchLink(array(
					"_query"=>array(
						"q"=>$this->_getQuery()
					)
				))
			));
		}
    }

    /**
     * prepare breadcrumb block
     * @return
     */
    protected function _getBlock() {

        if (is_null($this->_breadcrumbBlock)) {
            if (!($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs'))) {
                $breadcrumbsBlock = $this->getLayout()->createBlock('page/html_breadcrumbs', 'breadcrumbs');
            }
            $this->_breadcrumbBlock = is_null($breadcrumbsBlock)? 0:$breadcrumbsBlock;
        }
        return $this->_breadcrumbBlock;
    }

    public function getPathProp(){
        return $this->_getPath();
    }
    /**
     * Preparing layout
     *
     * @return Mage_Catalog_Block_Breadcrumbs
     */
    protected function _prepareLayout()
    {

        if(Mage::registry("bc_prepared")) {
            return $this;
        }

        $this->_prepareListingBreadcrumb();

        $title = array();
        $breadcrumbsBlock = $this->_getBlock();
        $path = $this->_getPath();
        foreach ($path as $name => $breadcrumb) {
            $breadcrumbsBlock->addCrumb($name, $breadcrumb);
            $title[] = $breadcrumb['label'];
        }

        if ($headBlock = $this->getLayout()->getBlock('head')) {
            if ($this->_isSearchContext()) {
                $helperZSS = Mage::helper('zolagosolrsearch');
                if ($helperZSS->getNumFound()) {
                    $query = $helperZSS->getSolrRealQ();  
                } else {
                    $query = $helperZSS->getQueryText();  
                }
                $title = $helperZSS->__('Search results for:').' '.$query;
            } else {
                $title = join($this->getTitleSeparator(), array_reverse($title));
            }
            $headBlock->setTitle($title);
        }

        // Do not prapare bc again
        Mage::register("bc_prepared", true);
        return $this;
    }
}
