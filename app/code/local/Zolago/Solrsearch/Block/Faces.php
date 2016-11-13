<?php
class Zolago_Solrsearch_Block_Faces extends SolrBridge_Solrsearch_Block_Faces
{
    const MODE_CATEGORY = 1;
    const MODE_SEARCH = 2;

    const PRICE_FACET_TRANSLATED = "price_facet";

    const DEFAULT_RNDERER = "zolagosolrsearch/faces_enum";

    protected $_priceFacet;

    protected static $_itemId =  array();

    public function _construct() {
        parent::_construct();
        // Override tmpalte
        $this->setTemplate('zolagosolrsearch/standard/searchfaces.phtml');
    }

    public function isShoppingOptionsActive() {
        return true;
    }

    /**
     *
     * @param
     * @return
     */
    protected function _getPriceFacet() {
        if (is_null($this->_priceFacet)) {
            $this->_priceFacet = Mage::helper('zolagosolrsearch')->getPriceFacet();
        }
        return $this->_priceFacet;
    }

    /**
     * @param stirng $facetCode
     * @return string
     */
    public function getFacetLabel($facetCode) {

        if($facetCode==Zolago_Solrsearch_Model_Solr::FLAGS_FACET) {
            return Mage::helper('zolagosolrsearch')->__('Product Flags');
        }
        if(in_array($facetCode, array("campaign_info_id_facet", "campaign_regular_id_facet"))) {
            return Mage::helper('zolagosolrsearch')->__('Campaign');
        }
        return parent::getFacetLabel($facetCode);
    }


    public function prepareSolrData() {
        //return parent::prepareSolrData();
        $data = $this->getListModel()->getSolrData();
        $this->solrData = $data;
        $this->solrModel = Mage::getModel('solrsearch/solr');
        $this->solrModel->setSolrData($data);
    }


    public function _prepareLayout() {
        if($this->getSkip()) {
            return parent::_prepareLayout();
        }
        // Build breadcrumbs
        if($this->getMode()==self::MODE_SEARCH) {

            $helper = Mage::helper('solrsearch');
            $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');

            if ($breadcrumbs) {

                $root_category_id = Mage::app()->getStore()->getRootCategoryId();

                $params = $this->getRequest()->getParams();
                if(isset($params['parent_cat_id'])) {

                    $parent_cat_id = $params['parent_cat_id'];

                    // Remove fq from params
                    // Clicking on breadcrumb link resets all filters
                    if(isset($params['fq'])) unset($params['fq']);
                    if(isset($params['parent_cat_id'])) unset($params['parent_cat_id']);

                    $urlParams = array();
                    $urlParams['_current']  = false;
                    $urlParams['_escape']   = true;
                    $urlParams['_use_rewrite']   = true;

                    $urlParams['_query']    = $params;

                    $search_link = Mage::getUrl($this->getUrlRoute(), $urlParams);

                    // Make 'search' breadcrumb a link
                    $search_title = $this->__("Search results for: '%s'", $this->helper('catalogsearch')->getQueryText());
                    $breadcrumbs->addCrumb('search', array('label' => $search_title, 'title' => $search_title, 'link' => $search_link));

                    $category = Mage::getModel('catalog/category')->load($parent_cat_id);

                    if($category) {

                        // Add breadcrumbs for parent categories
                        $parent_category = $category->getParentCategory();

                        if($parent_category) {

                            $category_breadcrumbs = array();

                            while($parent_category->getId() != $root_category_id) {
                                $parent_category_name = $parent_category->getName();

                                $children_category_ids = $parent_category->getResource()->getChildren($parent_category, true);
                                if($children_category_ids) {

                                    foreach($children_category_ids as $child_cat_id) {

                                        $ids[] = $child_cat_id;

                                    }
                                }

                                $params['fq'] = array('category_id' => $ids);
                                $params['parent_cat_id'] = $parent_category->getId();

                                $urlParams = array();
                                $urlParams['_current']  = false;
                                $urlParams['_escape']   = true;
                                $urlParams['_use_rewrite']   = true;

                                $urlParams['_query']    = $params;

                                $category_link = Mage::getUrl($this->getUrlRoute(), $urlParams);

                                $bc = array(
                                          'key' => $parent_category_name,
                                          'data' => array('label'=>$helper->__($parent_category_name), 'title'=>$helper->__($parent_category_name), 'link'=>$category_link)
                                      );

                                $category_breadcrumbs[] = $bc;


                                $parent_category = $parent_category->getParentCategory();
                            }

                            // Loop though creadcrumbs and add them to existing breadcrumb
                            if(sizeof($category_breadcrumbs) > 0) {

                                $category_breadcrumbs = array_reverse($category_breadcrumbs);

                                foreach($category_breadcrumbs as $category_breadcrumb) {
                                    $breadcrumbs->addCrumb($category_breadcrumb['key'], $category_breadcrumb['data']);
                                }

                            }
                        }

                        // Add breadcrumb for current category
                        $breadcrumbs->addCrumb($category->getName(), array('label'=>$helper->__($category->getName()), 'title'=>$helper->__($category->getName())));
                    }
                }
            }
        }

        return parent::_prepareLayout();
    }

    public function setSolrData($data) {
        $this->solrData = $data;
    }

    protected function _checkFilterDepedncy($filter) {

        if(!($parentAttributeId = $filter->getParentAttributeId())) {
            return true;
        }
        if($parentAttributeCode = $this->getAttributeCodeById($parentAttributeId)) {
            return $this->isFilterActive($parentAttributeCode);
        }
        return false;
    }

    protected function getFilterQuery()
    {
        $filterQuery = parent::getFilterQuery();

        if(isset($filterQuery['category_id'])) {
            unset($filterQuery['category_id']);
        }
        return $filterQuery;
    }

    public function isFilterActive($attrCode) {
        $filterQuery = $this->getFilterQuery();
        if (isset($filterQuery[$attrCode."_facet"])) {
            return true;
        }
        return false;
    }

    public function getDependAttributes($attributeCode) {
        $depends = array();
        $attributeId = $this->getAttributeIdByCode($attributeCode);
        foreach($this->getFilterCollection() as $fiter) {
            if($attributeId && $fiter->getParentAttributeId()==$attributeId) {
                $depends[] = $this->getAttributeCodeById($fiter->getAttributeId());
            }
        }
        return $depends;
    }

    protected function _mapUrlToJson($urlArray) {
        if(isset($urlArray['_query']) && is_array($urlArray['_query'])) {
            $urlArray = $urlArray['_query'];
        } else {
            $urlArray = array();
        }
        // Clear null values
        foreach($urlArray as $key=>$value) {
            if($value===null) {
                unset($urlArray[$key]);
            }
        }
        if(empty($urlArray)) {
            $urlArray = new stdClass; //force as object
        }
        return Mage::helper("core")->jsonEncode($urlArray);
    }

    public function getRemoveAllUrl() {
        $queryData =  $this->_parseRemoveAllUrl();
        if ($rawUrl = $this->getRedirectUrl($queryData)) {
            $url = $rawUrl;
        } else {
            $url = Mage::getUrl($this->getUrlRoute(), $this->_parseRemoveAllUrl());
        }
        return $url;
    }

    /**
     * Return path for current location
     * @return string
     */
    public function getUrlRoute() {
        return $this->getListModel()->getCurrentUrlPath();
    }

    /**
     * @return Zolago_Solrsearch_Model_Catalog_Product_List
     */
    public function getListModel() {
        return Mage::getSingleton('zolagosolrsearch/catalog_product_list');
    }

    public function getRemoveAllJson() {
        return $this->_mapUrlToJson($this->_parseRemoveAllUrl());
    }


    /**
     * overriding q
     * @param array $paramss
     * @return array
     */

    protected function _parseUrlParams(&$paramss) {

        $_solrDataArray = $this->getSolrData();
        if( isset($_solrDataArray['responseHeader']['params']['q']) && !empty($_solrDataArray['responseHeader']['params']['q']) ) {
            if (isset($paramss['q']) && $paramss['q'] != $_solrDataArray['responseHeader']['params']['q']) {
                $paramss['q'] = $_solrDataArray['responseHeader']['params']['q'];
            }
        }
    }

    protected function _parseRemoveAllUrl() {

        $paramss = $this->getRequest()->getParams();
        $this->_parseUrlParams($paramss);
        $finalParams = array();
        if(isset($paramss['q'])) {
            $finalParams['q'] = $paramss['q'];
        }
        if(isset($paramss['page'])) {
            $finalParams['page'] = $paramss['page'];
        }
        if(isset($paramss['sort'])) {
            $finalParams['sort'] = $paramss['sort'];
        }
        if(isset($paramss['dir'])) {
            $finalParams['dir'] = $paramss['dir'];
        }

        if(isset($paramss['fq']['category_id'])) {
            $finalParams['fq']['category_id'] = $paramss['fq']['category_id'];
        }
        if(isset($paramss['parent_cat_id'])) {
            $finalParams['parent_cat_id'] = $paramss['parent_cat_id'];
        }

        // add scat
        if (isset($paramss['scat'])) {
            $finalParams['scat'] = $paramss['scat'];
        }

        $urlParams = array();
        //$urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;

        if (isset($finalParams)) {

            if (Mage::app()->getRequest()->getRouteName() == 'catalog') {
                if (isset($finalParams['q'])) {
                    unset($finalParams['q']);
                }
                if (isset($finalParams['id'])) {
                    unset($finalParams['id']);
                }
            }


            $urlParams['_query']    = $this->processFinalParams($finalParams);
        }

        if($this->getListModel()->isCategoryMode()) {
            $urlParams['_direct'] = $this->getListModel()->getUrlPathForCategory();
        }

        return $urlParams;
    }

    public function getItemId($attributeCode, $item) {
        if (empty(self::$_itemId[$attributeCode][$item])) {
            self::$_itemId[$attributeCode][$item] = $attributeCode.'_'.uniqid();
        }
        return self::$_itemId[$attributeCode][$item];
    }

    public function getRemoveFacesUrl($key,$value)
    {
        $queryData =  $this->_parseRemoveFacesUrl($key, $value);
        if ($rawUrl = $this->getRedirectUrl($queryData)) {
            $url = $rawUrl;
        } else {
            $url =  Mage::getUrl($this->getUrlRoute(), $queryData);
        }
        return $url;
    }

    public function getRemoveFacesJson($key,$value) {
        return $this->_mapUrlToJson($this->_parseRemoveFacesUrl($key, $value));
    }


    public function _parseRemoveFacesUrl($key,$value)
    {
        $paramss = $this->getRequest()->getParams();
        $this->_parseUrlParams($paramss);
        $finalParams = $paramss;

        if (!is_array($key)) {
            $key = array($key);
        }

        if (!is_array($value)) {
            $value = array($value);
        }




        foreach ($key as $item)
        {
            if($item=="price") {
                if ($this->getRequest()->getParam('slider')) {
                    $finalParams['slider']=null;
                }
            }
            if (isset($finalParams['fq'][$item]) && !is_array($finalParams['fq'][$item]) && !empty($finalParams['fq'][$item])) {
                unset($finalParams['fq'][$item]);
                if ($item == 'category' && isset($finalParams['fq'][$item.'_id'])) {
                    unset($finalParams['fq'][$item.'_id']);
                }
            } else if (isset($finalParams['fq'][$item]) && is_array($finalParams['fq'][$item]) && count($finalParams['fq'][$item]) > 0) {
                foreach ($finalParams['fq'][$item] as $k=>$v) {
                    foreach($value as $_value) {
                        if ($v == $_value) {
                            unset($finalParams['fq'][$item][$k]);
                            if ($item == 'category' && isset($finalParams['fq'][$item.'_id']) && isset($finalParams['fq'][$item.'_id'][$k])) {
                                unset($finalParams['fq'][$item.'_id'][$k]);
                            }
                        }
                    }

                }
            }

            // Unset all depended fileds
            foreach($this->getDependAttributes($item) as $depend) {
                if(isset($finalParams['fq'][$depend])) {
                    if((is_array($paramss['fq'][$item]) && count($paramss['fq'][$item])<2) || !is_array($paramss['fq'][$item])) {
                        unset($finalParams['fq'][$depend]);
                    }
                }
            }
        }
        // clear filters
        if (!empty($finalParams['fq'])) {
            foreach ($finalParams['fq'] as $filter=>&$values) {
                if (is_array($values)) {
                    $values = array_values($values);
                }
            }
        }
        $urlParams = array();
        $urlParams['_current']  = false;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;


        if (isset($finalParams)) {
            if ($this->getListModel()->getMode()==Zolago_Solrsearch_Model_Catalog_Product_List::MODE_CATEGORY) {
                if (isset($finalParams['q'])) {
                    unset($finalParams['q']);
                }
                if (isset($finalParams['id'])) {
                    unset($finalParams['id']);
                }
            }

            $urlParams['_query']    = $this->processFinalParams($finalParams);
        }


        if($this->getListModel()->isCategoryMode()) {
            $urlParams['_direct'] = $this->getListModel()->getUrlPathForCategory();
        }
        if($this->getListModel()->isSearchMode()) {
            $urlParams['_direct'] = $this->getListModel()->getUrlPathForCategory();
        }

        return $urlParams;
    }

    public function getRemoveAllFacesUrl($key)
    {
        $queryData =  $this->_parseRemoveAllFacesUrl($key);
        if ($rawUrl = $this->getRedirectUrl($queryData)) {
            $url = $rawUrl;
        } else {
            $url = Mage::getUrl($this->getUrlRoute(), $queryData);
        }
        return $url;
    }

    public function _parseRemoveAllFacesUrl($key)
    {
        $paramss = $this->getRequest()->getParams();
        $this->_parseUrlParams($paramss);
        $finalParams = $paramss;

        if (!is_array($key)) {
            $key = array($key);
        }


        foreach ($key as $item)
        {
            if($item=="price" && $this->getRequest()->getParam('slider')) {
                $finalParams['slider']=null;
            }
            if (isset($finalParams['fq'][$item])) {
                unset($finalParams['fq'][$item]);
            }
            // Unset all depended fields
            foreach($this->getDependAttributes($item) as $depend) {
                if(isset($finalParams['fq'][$depend])) {
                    unset($finalParams['fq'][$depend]);
                }
            }
        }

        $urlParams = array();
        $urlParams['_current']  = false;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;


        if (isset($finalParams)) {
            if ($this->getListModel()->getMode()==Zolago_Solrsearch_Model_Catalog_Product_List::MODE_CATEGORY) {
                if (isset($finalParams['q'])) {
                    unset($finalParams['q']);
                }
                if (isset($finalParams['id'])) {
                    unset($finalParams['id']);
                }
            }

            $urlParams['_query']    = $this->processFinalParams($finalParams);
        }

        if($this->getListModel()->isCategoryMode()) {
            $urlParams['_direct'] = $this->getListModel()->getUrlPathForCategory();
        }

        return $urlParams;
    }


    /**
     * merge special blocks like category, price, typ flag, rating
     * @return array(attrCode=>blockObject, ...)
     */
    public function getFilterBlocks() {
        $solrData = $this->getSolrData();
        $additionalBlocks = array(
                                'category' => $this->getCategoryBlock($solrData),
                                'price' =>$this->getPriceBlock($solrData),
                                'flag' => $this->getFlagBlock($solrData),
                                'rating' => $this->getRatingBlock($solrData),
                                'super_attributes' => $this->getSuperAttributesBlock($solrData),
                            );

        //die('test');
        // Fix for landing pages and campaigns
        $fq = $this->getRequest()->getParam('fq');
        $campInfo = isset($fq['campaign_info_id']) ? $fq['campaign_info_id'] : null;
        $campRegular = isset($fq['campaign_regular_id']) ? $fq['campaign_regular_id'] : null;
        if (!empty($fq) && !empty($campInfo) && !isset($solrData['facet_counts']['facet_fields']['campaign_info_id_facet'])) {
            $solrData['facet_counts']['facet_fields']['campaign_info_id_facet'] = array($campInfo[0] => 1); // Don;t care about counts here
        }
        if (!empty($fq) && !empty($campRegular) && !isset($solrData['facet_counts']['facet_fields']['campaign_regular_id_facet'])) {
            $solrData['facet_counts']['facet_fields']['campaign_regular_id_facet'] = array($campRegular[0] => 1); // Don;t care about counts here
        }
        // End od fix

        $outBlock = $this->_getRegularFilterBlocks($solrData);

        // block order #484
        $finishBlock = array ();
        if ($additionalBlocks['category']) {
            $finishBlock[] = $additionalBlocks['category'];
        }
        foreach($outBlock as $block) {
            if($block) {
                $finishBlock[] = $block;
            }
        }
        if ($additionalBlocks['flag']) {
            $finishBlock[] = $additionalBlocks['flag'];
        }
        if ($additionalBlocks['rating']) {
            $finishBlock[] = $additionalBlocks['rating'];
        }
        if ($additionalBlocks['price']) {
            $finishBlock[] = $additionalBlocks['price'];
        }
        if ($additionalBlocks['super_attributes']) {
            $finishBlock[] = $additionalBlocks['super_attributes'];
        }

        foreach($finishBlock as $block) {
            $block->setFilterContainer($this);
            $block->setSolrModel($this->solrModel);
        }
        return $finishBlock;
    }

    /**
     * @param array $data
     * @param int $category_id
     * @param boolean $break Break when cagegory is found
     *
     * @return int
     */
    public function getCategoryCount($data, $category_id, $break = FALSE) {

        $count = 0;
        foreach($data as $key => $value) {

            $items = explode('/',$key);
            $current_category_id = (int)$items[count($items)-1];

            if($category_id == $current_category_id) {

                $count += $value;

                if($break) {
                    break;
                }
            }
        }

        return $count;
    }

    public function getCategoryBlock($solrData) {
        $facetFileds = array();
        if (isset($solrData['facet_counts']['facet_fields']) && is_array($solrData['facet_counts']['facet_fields'])) {
            $facetFileds = $solrData['facet_counts']['facet_fields'];
        }
        if(isset($facetFileds['category_facet'])) {
            $data = $facetFileds['category_facet'];
            $block = $this->getLayout()->createBlock($this->_getCategoryRenderer());
            $block->setParentBlock($this);
            $block->setFilterContainer($this);
            $data = $block->processCategoryData($data,false);
            $block->setAllItems($data);
            $block->setFacetKey("category_facet");
            return $block;
        }
        return null;
    }

    public function getPriceBlock($solrData) {
        if(in_array($this->getMode(),array(self::MODE_CATEGORY,self::MODE_SEARCH))  && !($this->getCurrentCategory()->getUsePriceFilter())) {
            return null;
        }
        $facetFileds = array();
        if (isset($solrData['facet_counts']['facet_fields']) && is_array($solrData['facet_counts']['facet_fields'])) {
            $facetFileds = $solrData['facet_counts']['facet_fields'];
        }
        $priceFacet = $this->_getPriceFacet();
        if(isset($facetFileds[$priceFacet])) {
            $data = $facetFileds[$priceFacet];
            $data = $this->_prepareMultiValues(Zolago_Solrsearch_Model_Solr::PRICE_CODE,$data);
            $block = $this->getLayout()->createBlock($this->_getPriceRenderer());
            $block->setParentBlock($this);
            $block->setAllItems($data);
            $block->setFacetKey("price_facet");
            $block->setAttributeCode("price");
            return $block;
        }
    }

    public function getFlagBlock($solrData) {
        // Only in category ?
        if(in_array($this->getMode(),array(self::MODE_CATEGORY,self::MODE_SEARCH))&& !$this->getCurrentCategory()->getUseFlagFilter()) {
            return null;
        }

        $facetFileds		= array();

        if (isset($solrData['facet_counts']['facet_fields']) && is_array($solrData['facet_counts']['facet_fields'])) {
            $facetFileds = $solrData['facet_counts']['facet_fields'];
        }
        if(isset($facetFileds[Zolago_Solrsearch_Model_Solr::FLAGS_FACET])) {
            $data = $facetFileds[Zolago_Solrsearch_Model_Solr::FLAGS_FACET];
            if($this->getSpecialMultiple()) {
                $data = $this->_prepareMultiValues(Zolago_Solrsearch_Model_Solr::FLAGS_CODE, $data);
            }
            ksort($data);

            if(empty($data))
                return null;

            $block = $this->getLayout()->createBlock($this->_getFlagRenderer());
            $block->setParentBlock($this);
            $block->setAllItems($data);
            $block->setFacetKey(Zolago_Solrsearch_Model_Solr::FLAGS_FACET);
            $block->setAttributeCode("flags");
            return $block;
        }
    }

    public function getSuperAttributesBlock($solrData) {
        $facetFileds = array();
        krumo($solrData,$facetFileds);
        krumo($solrData);
        if (isset($solrData['facet_counts']['facet_fields']) && is_array($solrData['facet_counts']['facet_fields'])) {
            $facetFileds = $solrData['facet_counts']['facet_fields'];
        }
        if(isset($facetFileds['super_attribute_facet'])) {
            $data = $facetFileds['super_attribute_facet'];
            krumo($data);

            /* @var $block Zolago_Solrsearch_Block_Faces_Superattribute */
            $block = $this->getLayout()
                ->createBlock("zolagosolrsearch/faces_superattribute");
            $block->setParentBlock($this);
            $block->setSolrData($solrData);
            $block->setAllItems($data);
            $block->setAttributeCode("super_attribute");
            $block->setFacetKey("super_attribute");
            return $block;
        }
    }
    public function getRatingBlock($solrData) {
        if(!$this->getCurrentCategory()->getUseReviewFilter()) {
            return null;
        }
        $facetFileds = array();
        if (isset($solrData['facet_counts']['facet_fields']) && is_array($solrData['facet_counts']['facet_fields'])) {
            $facetFileds = $solrData['facet_counts']['facet_fields'];
        }
        if(isset($facetFileds[Zolago_Solrsearch_Model_Solr::RATING_FACET])) {
            $data = $facetFileds[Zolago_Solrsearch_Model_Solr::RATING_FACET];

            if($this->getSpecialMultiple()) {
                $data = $this->_prepareMultiValues(Zolago_Solrsearch_Model_Solr::RATING_CODE, $data);
            }
            if(isset($data['No rating'])) {
                unset($data['No rating']);
            }

            //Remove Boolean "False" Values
            if (isset($data[Mage::helper('core')->__('No')])) {
                unset($data[Mage::helper('core')->__('No')]);
            }

            $block = $this->getLayout()->createBlock($this->_getRatingRenderer());
            $block->setParentBlock($this);
            $block->setAllItems($data);
            $block->setAttributeCode("product_rating");
            $block->setFacetKey("product_rating_facet");
            return $block;
        }
    }


    /**
     * @return string
     */
    protected function _getRatingRenderer() {
        return $this->_rewriteBlockType("zolagosolrsearch/faces_rating");
    }
    /**
     * @return string
     */
    protected function _getFlagRenderer() {
        return $this->_rewriteBlockType("zolagosolrsearch/faces_flag");
    }
    /**
     * @return string
     */
    protected function _getPriceRenderer() {
        return $this->_rewriteBlockType("zolagosolrsearch/faces_price");
    }
    /**
     * @return string
     */
    protected function _getCategoryRenderer() {
        return $this->_rewriteBlockType("zolagosolrsearch/faces_category");
    }
    /**
     * @return boolean
     */
    public function getSpecialMultiple() {
        return Mage::helper('solrsearch')->getSetting('allow_multiple_filter') > 0;
    }

    /**
     * @return array(attrCode=>blockObject, ...)
     */
    protected function _getRegularFilterBlocks(array $solrData) {
        $priceFieldName = Mage::helper('solrsearch')->getPriceFieldName();
        $facetFileds = array();
        $sorted = array();
        //krumo($solrData);
        if (isset($solrData['facet_counts']['facet_fields']) && is_array($solrData['facet_counts']['facet_fields'])) {
            $facetFileds = $solrData['facet_counts']['facet_fields'];
        }

        //Ignore the price_decimal
        if (isset($facetFileds[$priceFieldName])) {
            unset($facetFileds[$priceFieldName]);
        }

        //Ignore the category
        if(isset($facetFileds['category_facet'])) {
            unset($facetFileds['category_facet']);
        }

        foreach($facetFileds as $key=>$data) {

            $attrCode = $this->_extractAttributeCode($key);
            $block = null;
            $sortOrder = 0;
            switch ($attrCode) {
            // Skip special facets
            case "category_path":
            case "category_id":
            case "product_flag":
            case "is_new":
            case "is_bestseller":
            case "product_rating":
                continue 2;
                break;

            // Skip vendor facet in vendor scope
            case "udropship_vendor":
                if($this->getVendor()) {
                    continue 2;
                }
                break;
            }
            // In category mode

            $filter = $this->getFilterByAttribute($attrCode);

            // Skip attribs with no custom filter
            if(!$filter || !$filter->getId()) {
                // Always attach campaign information ( for landing pages )
                if ($attrCode != Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_ID_CODE &
                        $attrCode != Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE) {
                    continue;
                } else {
                    $filter = Mage::getModel("zolagocatalog/category_filter");
                    $filter->setCustomShowFlag(1);
                    $filter->setSortOrder(10000);
                }
            }

            // Check is filter depended - if not - skip
            if($filter->getParentAttributeId() && !$this->_checkFilterDepedncy($filter)) {
                continue;
            }

            // Is multiple values
            if($filter->getShowMultiple()) {
                $data = $this->_prepareMultiValues($attrCode, $data);
            }

            if(count($data)) {
                $renderer = $this->getDefaultRenderer();
                if($filter->getFrontendRenderer()) {
                    $renderer = $filter->getFrontendRenderer();
                }

                $block = $this->getLayout()->createBlock(
                             $this->_rewriteBlockType($renderer)
                         );
                /* @var $block Zolago_Solrsearch_Block_Faces_Abstract */


                if(! ($block instanceof  Zolago_Solrsearch_Block_Faces_Abstract)) {
                    throw new Exception("Unknow block type $renderer");
                }

                $block->setFilterModel($filter);
                $sortOrder = $filter->getSortOrder();
            }

            if($block) {
                $block->setAllItems($data);
                $block->setAttributeCode($attrCode);
                $block->setFacetKey($key);
                $block->setCustomShowFlag($filter->getCustomShowFlag());
                if(!isset($sorted[$sortOrder])) {
                    $sorted[$sortOrder] = array();
                }
                $sorted[$sortOrder][] = $block;
            }
        }

        ksort($sorted);

        $blocks = array();
        foreach($sorted as $ordered) {
            foreach($ordered as $block) {
                $blocks[] = $block;
            }
        }

        return $blocks;
    }

    protected function _createFacetFromAttributeCode($code) {
        if ($code == 'price') {
            return $this->_getPriceFacet();
        }
        return $code.'_facet';
    }
    protected function _extractAttributeCode($facet) {
        if ($facet == $this->_getPriceFacet()) {
            return 'price';
        }
        $out = substr($facet,0,-6);
//        $out = preg_replace("/_facet$/", "", $facet);
        return $out;
    }


    /**
     * @return string
     */
    public function getDefaultRenderer() {
        return $this->_rewriteBlockType(self::DEFAULT_RNDERER);
    }

    /**
     * @return int
     */
    public function getMode() {
        $q = Mage::app()->getRequest()->getParam('q', '');//queryText
        if(Mage::registry('is_search_mode')) {
            return self::MODE_SEARCH;
        }
        if(!Mage::registry('current_product') && empty($q)) {
            return self::MODE_CATEGORY;
        } else {
            Mage::register('is_search_mode', true);
            return self::MODE_SEARCH;
        }
    }

    /**
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory() {
        /** @var Zolago_Solrsearch_Helper_Data $helper */
        $helper = Mage::helper("zolagosolrsearch");
        return $helper->getCurrentCategory();
    }


    /**
     * @return Zolago_Catalog_Model_Resource_Category_Filter_Collection
     */
    public function getFilterCollection() {
        if(!$this->hasData("filter_collection")) {
            $collection = Mage::getResourceModel("zolagocatalog/category_filter_collection");
            /* @var $collection Zolago_Catalog_Model_Resource_Category_Filter_Collection */
            $collection->joinAttributeCode();
            if($this->getCurrentCategory()) {
                $category = $this->getCurrentCategory();
                $collection->addCategoryFilter($category);
                if (!count($collection)) {
                    $related = $category->getRelatedCategory();
                    if ($related) {
                        // check related category
                        $collection = Mage::getResourceModel("zolagocatalog/category_filter_collection");
                        /* @var $collection Zolago_Catalog_Model_Resource_Category_Filter_Collection */
                        $collection->joinAttributeCode();
                        $collection->addCategoryFilter($related);
                    }
                }
            }
            $this->setData("filter_collection", $collection);

        }
        return $this->getData("filter_collection");
    }


    public function getFilterByAttribute($attrCode) {
        if(!$this->hasData("filter_by_attribute")) {
            $attributeByCode = array();
            foreach($this->getFilterCollection() as $filter) {
                $attributeByCode[$filter->getAttributeCode()] = $filter;
            }
            $this->setData("filter_by_attribute", $attributeByCode);
        }
        return $this->getData("filter_by_attribute", $attrCode);
    }

    public function getAttributeCodeById($attrCode) {
        if(!$this->hasData("attribute_code_by_id")) {
            $attributeCodeById = array();
            foreach($this->getFilterCollection() as $filter) {
                $attributeCodeById[$filter->getAttributeId()] = $filter->getAttributeCode();
            }
            $this->setData("attribute_code_by_id", $attributeCodeById);
        }
        return $this->getData("attribute_code_by_id", $attrCode);
    }

    public function getAttributeIdByCode($attrCode) {
        if(!$this->hasData("attribute_id_by_code")) {
            $attributeCodeById = array();
            foreach($this->getFilterCollection() as $filter) {
                $attributeCodeById[$filter->getAttributeCode()] = $filter->getAttributeId();
            }
            $this->setData("attribute_id_by_code", $attributeCodeById);
        }
        return $this->getData("attribute_id_by_code", $attrCode);
    }

    /**
     * @return SolrBridge_Solrsearch_Model_Solr
     */
    protected function _getHelpedSolrModel() {
        if(!$this->getData("helped_solr_model")) {
            $this->setData("helped_solr_model", Mage::getModel('solrsearch/solr'));
        }
        return $this->getData("helped_solr_model");
    }




    /**
     * prepare multi values for filter
     *
     * @param string $attrCode attribute code
     * @param array $fallbackData
     * @return array
     */

    protected function _prepareMultiValues($attrCode, $fallbackData=array()) {
        // @todo check is filter already active?
        // If not dont try re-request
        //
        // Remove this key from query params\
        $req = Mage::app()->getRequest();

        $oldParams = $req->getParams();
        $params = $oldParams;
        $paramKey = $attrCode;
        if(isset($params['fq'][$paramKey])) {
            unset($params['fq'][$paramKey]);
        }
        $facetkey = $this->_createFacetFromAttributeCode($attrCode);
        $filters = $this->getFilterQuery();
        // Force unset category id
        if($paramKey=="category_path") {
            if(!isset($filters['category_id'])) {
                return $fallbackData;
            }
            if(isset($params['fq']['category_id'])) {
                unset($params['fq']['category_id']);
            }
            if(isset($params['fq']['category'])) {
                unset($params['fq']['category']);
            }
            // No data changed

        }
        elseif ($attrCode == Zolago_Solrsearch_Model_Solr::PRICE_CODE) {
            // if no changes then return
            if (!isset($oldParams['fq'][$paramKey])) {
                return $fallbackData;
            }
        }
        elseif(!isset($filters[$facetkey])) {
            return $fallbackData;
        }
        try {
            $model = $this->_getHelpedSolrModel();
            $queryText = Mage::helper('solrsearch')->getParam('q');
            $req->setParams($params);
            $result = $model->query($queryText);
            $req->setParams($oldParams);
            if(isset($result['facet_counts']['facet_fields'][$facetkey])) {
                return $result['facet_counts']['facet_fields'][$facetkey];
            }

        } catch(Exception $e) {
            Mage::logException($e);
        }

        return $fallbackData;

    }

    /**
     * @param array $params params to be set
     * @param array $paramss current params (if not set will take current params from current request)
     */
    public function getFacesUrl($params=array(), $paramss = NULL)
    {
        $queryData = $this->_parseQueryData($params, $paramss);
        if ($rawUrl = $this->getRedirectUrl($queryData)) {
            $url = rtrim($rawUrl,'/');
        } else {
            $url =  Mage::getUrl($this->getUrlRoute(), $queryData);
        }
        return $url;
    }



    /**
     * raw url without redirects, with filters only
     */

    public function getRedirectUrl($queryData)
    {
        $url = null;
        if ($this->getListModel()->isCategoryMode()) {

            $category = $this->getListModel()->getCurrentCategory();
            $path = $this->getUrlRoute();
            $id = $category->getId();
            $tmp = $queryData['_query'];


            //1. Check if LP
            /* @var $campaign Zolago_Campaign_Model_Campaign */
            $campaign = $category->getCurrentCampaign();

            if($campaign) {
                /* @var $landingPageHelper Zolago_Campaign_Helper_LandingPage */
                $landingPageHelper = Mage::helper("zolagocampaign/landingPage");
                $params = isset($queryData["_query"]) ? $queryData["_query"] : array();
                $url = $landingPageHelper->getLandingPageUrlByCampaign($campaign, FALSE, $params);
                return $url;
            }


            //2. Check GH_Rewrite exist

            /** @var GH_Rewrite_Helper_Data $rewriteHelper */
            $rewriteHelper = Mage::helper('ghrewrite');
            $url = $rewriteHelper->prepareRewriteUrl($path, $id, $tmp);


        }
        return $url;
    }
    /**
     * @param array $params params to be set
     * @param array $paramss current params (if not set will take current params from current request)
     */
    public function getFacesJson($params=array(), $paramss = NULL) {
        return $this->_mapUrlToJson($this->_parseQueryData($params, $paramss));
    }

    /**
     * @param array $params params to be set
     * @param array $paramss current params (if not set will take current params from current request)
     */
    protected function _parseQueryData($params=array(), $paramss = NULL)
    {

        $_solrDataArray = $this->getSolrData();

        $paramss = Mage::app()->getRequest()->getParams();
        $finalParams = array();

        $finalParams = array_merge($paramss, $params);

        if( isset($_solrDataArray['responseHeader']['params']['q']) && !empty($_solrDataArray['responseHeader']['params']['q']) ) {
            if (isset($paramss['q']) && $paramss['q'] != $_solrDataArray['responseHeader']['params']['q']) {
                $paramss['q'] = $_solrDataArray['responseHeader']['params']['q'];
            }
        }

        foreach ($params as $key=>$item) {
            $key = trim($key);

            if( in_array($key, array('min', 'max')) ) {
                if (isset($paramss[$key])) {
                    unset($paramss[$key]);
                    $finalParams = array_merge_recursive($params, $paramss);
                }
            }
            if ($key == 'fq') {
                foreach ($item as $k=>$v) {
                    if (isset($paramss[$key][$k]) && $v == $paramss[$key][$k]) {

                    } else {
                        if( $k == 'price' && isset($paramss[$key][$k])/* || $k == 'category' || $k == 'category_id'*/) {
                            unset($paramss[$key][$k]);
                        }

                        $finalParams = array_merge_recursive($params, $paramss);

                    }
                }
            }
        }

        if (isset($finalParams['p'])) {
            $finalParams['p'] = 1;
        }

        if (isset($params['scat'])) {
            $finalParams['scat'] = $params['scat'];
        }

        if (isset($paramss['q'])) {

            $finalParams['q'] = $paramss['q'];

        }

        if (isset($finalParams['fq'])) {
            if(isset($finalParams['fq']['category_id']) && is_array($finalParams['fq']['category_id'])) {
                $finalParams['fq']['category_id'] = array_unique($finalParams['fq']['category_id']);
            }
        }

        if(isset($finalParams['Szukaj_x'])) {
            unset($finalParams['Szukaj_x']);
        }
        if(isset($finalParams['Szukaj_y'])) {
            unset($finalParams['Szukaj_y']);
        }

        $urlParams = array();
        $urlParams['_current']  = false;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;

        if (sizeof($finalParams) > 0) {

            if ($this->getListModel()->isCategoryMode()) {
                if (isset($finalParams['q'])) {
                    unset($finalParams['q']);
                }
                if (isset($finalParams['id'])) {
                    unset($finalParams['id']);
                }
            }

            $urlParams['_query']    = $this->processFinalParams($finalParams);
        }

//		if($this->getListModel()->isCategoryMode()){
        $urlParams['_direct'] = $this->getListModel()->getUrlPathForCategory();
//		}



        return $urlParams;
    }

    /**
     *
     * @param array $params
     * @return array
     */
    public function processFinalParams(array $params = array()) {
        /** @var $helper Zolago_Solrsearch_Helper_Data */
        $helper =  Mage::helper("zolagosolrsearch");
        $params = $helper->processFinalParams($params);

        /** @var GH_Rewrite_Helper_Data $rewriteHelper */
        $rewriteHelper = Mage::helper('ghrewrite');
        $rewriteHelper->sortParams($params);

        return $rewriteHelper->clearParams($params);
    }

    /**
     * @return ZolagoOs_OmniChannel_Model_Vendor|null
     */
    public function getVendor() {
        if(!$this->hasData("vendor")) {
            $this->setData("vendor", Mage::helper('umicrosite')->getCurrentVendor());
        }
        return $this->getData("vendor");
    }

    public function getCleanFlagFacetData()
    {
        $cleanSolrData = $this->solrModel->prepareCleanFlagQueryData()->execute();

        $facetCleanFileds	= array();
        $productFlagFacet	= array();
        $bestsellerFacet	= array();
        $isNewFacet			= array();

        if (isset($cleanSolrData['facet_counts']['facet_fields']) && is_array($cleanSolrData['facet_counts']['facet_fields'])) {
            $facetCleanFileds = $cleanSolrData['facet_counts']['facet_fields'];
        }

        if (isset($facetCleanFileds['product_flag_facet'])) {
            $productFlagFacet	= $facetCleanFileds['product_flag_facet'];
        }

        //Remove Boolean "False" Values
        if (isset($productFlagFacet[Mage::helper('core')->__('No')])) {
            unset($productFlagFacet[Mage::helper('core')->__('No')]);
        }

        if (!isset($productFlagFacet[Mage::helper('zolagocatalog')->__('Promotion')])) {
            $productFlagFacet[Mage::helper('zolagocatalog')->__('Promotion')] = 0;
        }

        if (!isset($productFlagFacet[Mage::helper('zolagocatalog')->__('Sale')])) {
            $productFlagFacet[Mage::helper('zolagocatalog')->__('Sale')] = 0;
        }

        if (isset($facetCleanFileds['is_bestseller_facet'][Mage::helper('core')->__('Yes')])) {
            $bestsellerFacet	= array(Mage::helper('zolagosolrsearch')->__('Bestseller') => $facetCleanFileds['is_bestseller_facet'][Mage::helper('core')->__('Yes')]);
        } else {
            $bestsellerFacet	= array(Mage::helper('zolagosolrsearch')->__('Bestseller') => 0);
        }

        if (isset($facetCleanFileds['is_new_facet'][Mage::helper('core')->__('Yes')])) {
            $isNewFacet			= array(Mage::helper('zolagosolrsearch')->__('New') => $facetCleanFileds['is_new_facet'][Mage::helper('core')->__('Yes')]);
        } else {
            $isNewFacet	= array(Mage::helper('zolagosolrsearch')->__('New') => 0);
        }

        return array_merge($productFlagFacet, $bestsellerFacet, $isNewFacet);
    }

    /**
     * Do optional rewrite block filter
     * @param string $block
     * @return string
     */
    protected function _rewriteBlockType($block) {
        return $block;
    }


    /**
     * reformat price filter
     * @param string $facetPriceRange
     * @return string
     */
    public function formatFacetPrice($facetPriceRange) {
        $priceArray = explode('TO', $facetPriceRange);

        $formattedPriceRange = $facetPriceRange;

        if (isset($priceArray[0]) && isset($priceArray[1])) {
            $currencySymbol = Mage::app()->getLocale()->currency(
                                  Mage::app()->getStore()->getCurrentCurrencyCode()
                              )->getSymbol();
            $currencyPositionSetting = Mage::helper('solrsearch')->getSetting('currency_position');


            if($priceArray[0]==="" && $priceArray[1]!=="") {
                if($currencyPositionSetting > 0) {
                    $value = $currencySymbol.' '.trim($priceArray[1]);
                } else {
                    $value = trim($priceArray[1]) . ' ' .$currencySymbol;
                }
                return  Mage::helper("zolagosolrsearch")->__("less than") . ' ' . $value;
            }
            elseif($priceArray[1]==="") {
                if($currencyPositionSetting > 0) {
                    $value = $currencySymbol.' '.trim($priceArray[0]);
                } else {
                    $value = trim($priceArray[0]) . ' ' .$currencySymbol;
                }
                return  Mage::helper("zolagosolrsearch")->__("greater than") . ' ' . $value;
            }


            if($currencyPositionSetting > 0) {
                $fromValue = $currencySymbol.' '.trim($priceArray[0]);
                $toValue = $currencySymbol.' '.trim($priceArray[1]);
            } else {
                $fromValue = trim($priceArray[0]) . ' ' .$currencySymbol;
                $toValue = trim($priceArray[1]) . ' ' .$currencySymbol;
            }

            $formattedPriceRange = $fromValue . ' ' .
                                   Mage::helper("zolagosolrsearch")->__("to")  . ' ' . $toValue;

        }
        return $formattedPriceRange;
    }


}