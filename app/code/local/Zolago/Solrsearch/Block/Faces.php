<?php
class Zolago_Solrsearch_Block_Faces extends SolrBridge_Solrsearch_Block_Faces
{
    const MODE_CATEGORY = 1;
    const MODE_SEARCH = 2;

    const DEFAULT_RNDERER = "zolagosolrsearch/faces_enum";


    public function _construct() {
        parent::_construct();
        // Override tmpalte
        $this->setTemplate('zolagosolrsearch/standard/searchfaces.phtml');
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
            if($fiter->getParentAttributeId()==$attributeId) {
                $depends[] = $this->getAttributeCodeById($fiter->getAttributeId());
            }
        }
        return $depends;
    }

    public function getRemoveFacesUrl($key,$value)
    {
        $paramss = $this->getRequest()->getParams();

        $finalParams = $paramss;

        if (!is_array($key)) {
            $key = array($key);
        }

        if (!is_array($value)) {
            $value = array($value);
        }


        foreach ($key as $item)
        {
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



        $urlParams = array();
        $urlParams['_current']  = true;
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

            $urlParams['_query']    = $finalParams;
        }

        return Mage::getUrl('*/*/*', $urlParams);
    }

    /**
     * merge special blocks like category, price, typ flag, rating
     * @return array(attrCode=>blockObject, ...)
     */
    public function getFilterBlocks() {

        $solrData = $this->getSolrData();
        $outBlock = $this->_getRegularFilterBlocks($solrData);
        $additionalBlocks = array(
                                $this->getCategoryBlock($solrData),
                                $this->getPriceBlock($solrData),
                                $this->getFlagBlock($solrData),
                                $this->getRatingBlock($solrData),
                            );
        $additionalBlocks = array_reverse($additionalBlocks);
        foreach($additionalBlocks as $block) {
            if($block) {
                array_unshift($outBlock, $block);
            }
        }
        foreach($outBlock as $block) {
            $block->setFilterContainer($this);
            $block->setSolrModel($this->solrModel);
        }
        return $outBlock;
    }

    protected function _processCategoryData($data) {
        $out = array();
        // change solr response
        $category = $this->getCurrentCategory();
        $only = array();        
        if ($category) {
            $list = $category->getCategories($category->getId(),1);
            foreach ($list as $cat) {
                $only[] = $cat->getId();
            }
        }        
        foreach ($data as $key=>$val) {
            $items = explode('/',$key);
            if (in_array($items[count($items)-1],$only)) {
                $out[$key] = $val;
            }
        }
        return $out;
    }

    public function getCategoryBlock($solrData) {
        $facetFileds = array();
        if (isset($solrData['facet_counts']['facet_fields']) && is_array($solrData['facet_counts']['facet_fields'])) {
            $facetFileds = $solrData['facet_counts']['facet_fields'];
        }
        if(isset($facetFileds['category_path'])) {
            $data = $facetFileds['category_path'];
            if($this->getSpecialMultiple()) {
                $data = $this->_prepareMultiValues('category_path', $data);
            }
            $data = $this->_processCategoryData($data);
            $block = $this->getLayout()->createBlock($this->_getCategoryRenderer());
            $block->setParentBlock($this);
            $block->setAllItems($data);
            $block->setFacetKey("category_path");
            return $block;
        }
        return null;
    }

    public function getPriceBlock($solrData) {
        if($this->getMode()==self::MODE_CATEGORY&& !$this->getCurrentCategory()->getUsePriceFilter()) {
            return null;
        }
        $block = $this->getLayout()->createBlock($this->_getPriceRenderer());
        $block->setParentBlock($this);
        return $block;
    }

    public function getFlagBlock($solrData) {
        if($this->getMode()==self::MODE_CATEGORY&& !$this->getCurrentCategory()->getUseFlagFilter()) {
            return null;
        }
        $facetFileds		= array();
        $bestsellerFacet	= array();
        $isNewFacet			= array();
        if (isset($solrData['facet_counts']['facet_fields']) && is_array($solrData['facet_counts']['facet_fields'])) {
            $facetFileds = $solrData['facet_counts']['facet_fields'];
        }

        if (isset($facetFileds['is_bestseller_facet'][Mage::helper('core')->__('Yes')])) {
            $bestsellerFacet	= array(Mage::helper('zolagosolrsearch')->__('Bestseller') => $facetFileds['is_bestseller_facet'][Mage::helper('core')->__('Yes')]);
        }

        if (isset($facetFileds['is_new_facet'][Mage::helper('core')->__('Yes')])) {
            $isNewFacet			= array(Mage::helper('zolagosolrsearch')->__('New') => $facetFileds['is_new_facet'][Mage::helper('core')->__('Yes')]);
        }

        if(isset($facetFileds['product_flag_facet'])) {
            $data = $facetFileds['product_flag_facet'];
            if($this->getSpecialMultiple()) {
                $data = $this->_prepareMultiValues('product_flag_facet', $data);
            }
            $data = array_merge($facetFileds['product_flag_facet'], $bestsellerFacet, $isNewFacet);

            $block = $this->getLayout()->createBlock($this->_getFlagRenderer());
            $block->setParentBlock($this);
            $block->setAllItems($data);
            $block->setAttributeCode("product_flag");
            $block->setFacetKey("product_flag_facet");
            return $block;
        }
    }

    public function getRatingBlock($solrData) {
        if($this->getMode()==self::MODE_CATEGORY&& !$this->getCurrentCategory()->getUseReviewFilter()) {
            return null;
        }
        $facetFileds = array();
        if (isset($solrData['facet_counts']['facet_fields']) && is_array($solrData['facet_counts']['facet_fields'])) {
            $facetFileds = $solrData['facet_counts']['facet_fields'];
        }
        if(isset($facetFileds['product_rating_facet'])) {
            $data = $facetFileds['product_rating_facet'];

            if($this->getSpecialMultiple()) {
                $data = $this->_prepareMultiValues('product_rating_facet', $data);
            }
            if(isset($data['No rating'])) {
                unset($data['No rating']);
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
        return $this->getDefaultRenderer();
    }
    /**
     * @return string
     */
    protected function _getFlagRenderer() {
        return "zolagosolrsearch/faces_flag";
    }
    /**
     * @return string
     */
    protected function _getPriceRenderer() {
        return "zolagosolrsearch/faces_price";
    }
    /**
     * @return string
     */
    protected function _getCategoryRenderer() {
        return "zolagosolrsearch/faces_category";
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

        if (isset($solrData['facet_counts']['facet_fields']) && is_array($solrData['facet_counts']['facet_fields'])) {
            $facetFileds = $solrData['facet_counts']['facet_fields'];
        }

        //Ignore the price_decimal
        if (isset($facetFileds[$priceFieldName])) {
            unset($facetFileds[$priceFieldName]);
        }

        // Category mode
        foreach($facetFileds as $key=>$data) {

            $attrCode = $this->_extractAttributeCode($key);
            $block = null;
            $sortOrder = 0;

            switch ($key) {
                // Skip special facets
            case "category_path":
            case "category_id":
            case "product_flag":
            case "product_rating":
                continue 2;
                break;
            }

            // In category mode
            if($this->getMode()==self::MODE_CATEGORY) {
                $filter = $this->getFilterByAttribute($attrCode);

                // Skip attribs with no custom filter
                if(!$filter || !$filter->getId()) {
                    continue;
                }

                // Check is filter depended - if not - skip
                if($filter->getParentAttributeId() && !$this->_checkFilterDepedncy($filter)) {
                    continue;
                }

                // Is multiple values
                if($filter->getShowMultiple()) {
                    $data = $this->_prepareMultiValues($key, $data);
                }

                if(count($data)) {
                    $renderer = $this->getDefaultRenderer();
                    if($filter->getFrontendRenderer()) {
                        $renderer = $filter->getFrontendRenderer();
                    }

                    $block = $this->getLayout()->createBlock($renderer);
                    /* @var $block Zolago_Solrsearch_Block_Faces_Abstract */


                    if(! ($block instanceof  Zolago_Solrsearch_Block_Faces_Abstract)) {
                        throw new Exception("Unknow block type $renderer");
                    }

                    $block->setFilterModel($filter);
                    $sortOrder = $filter->getSortOrder();
                }
            } else {
                // Search mode - unknow category filters
                if(count($data)) {
                    $block= $this->getLayout()->createBlock(
                                $this->getDefaultRenderer()
                            );
                }
            }

            if($block) {
                $block->setAllItems($data);
                $block->setAttributeCode($attrCode);
                $block->setFacetKey($key);
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

    protected function _extractAttributeCode($facet) {
        return preg_replace("/_facet$/", "", $facet);
    }


    /**
     * @return string
     */
    public function getDefaultRenderer() {
        return self::DEFAULT_RNDERER;
    }

    /**
     * @return int
     */
    public function getMode() {
        if($this->getCurrentCategory() && !Mage::registry('current_product')) {
            return self::MODE_CATEGORY;
        }
        return self::MODE_SEARCH;
    }

    /**
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory() {
		if(Mage::registry('current_category')){
			return Mage::registry('current_category');
		}
		return  Mage::registry('vendor_current_category');
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
                $collection->addCategoryFilter($this->getCurrentCategory());
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




    protected function _prepareMultiValues($facetkey, $fallbackData=array()) {
        // @todo check is filter already active?
        // If not dont try re-request
        //
        // Remove this key from query params\
        $req = Mage::app()->getRequest();

        $oldParams = $req->getParams();
        $params = $oldParams;

        $paramKey = $this->_extractAttributeCode($facetkey);

        if(isset($params['fq'][$paramKey])) {
            unset($params['fq'][$paramKey]);
        }

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

    public function getFacesUrl($params=array())
    {
        $_solrDataArray = $this->getSolrData();

        $paramss = $this->getRequest()->getParams();

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
        if (isset($finalParams['fq'])) {
            if(isset($finalParams['fq']['category_id']) && is_array($finalParams['fq']['category_id'])) {
                $finalParams['fq']['category_id'] = array_unique($finalParams['fq']['category_id']);
            }
            if(isset($finalParams['fq']['category']) && is_array($finalParams['fq']['category'])) {
                $finalParams['fq']['category'] = array_unique($finalParams['fq']['category']);
            }
        }

        $urlParams = array();
        $urlParams['_current']  = true;
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

            $urlParams['_query']    = $finalParams;
        }
        return $this->getUrl('*/*/*', $urlParams);
    }
}