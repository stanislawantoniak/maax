<?php
/**
 * @method Zolago_Catalog_Model_Category_Filter getFilterModel() Description
 * @method Zolago_Solrsearch_Block_Faces getFilterContainer() Description
 */
abstract class Zolago_Solrsearch_Block_Faces_Abstract extends Mage_Core_Block_Template
{
    protected $_solrData;
    protected $_filterQuery;
    protected $_solrModel;

    protected $_active;

    protected $_allItems;

    protected $_canShow;
    protected $_maxCount = 0;
    protected $_canShowHidden;
    protected $_maxCountHidden = 0;
    
    public function __construct()
    {
        $this->setTemplate('zolagosolrsearch/standard/searchfaces/enum.phtml');
    }


    /**
     * preparing item (calcuate all values before display)
     * @param
     * @return
     */

    protected function _prepareItemValue($key,$val) {
        return array (
                   'item' => $key,
                   'count' => $val,
                   'itemId' => $this->getItemId($key),
                   'url' => $this->getItemUrl($key),
                   'params' => $this->getItemJson($key),
                   'active' => $this->isItemActive($key),
                   'name' => $this->getItemName($key),
                   'value' => $this->getItemValue($key),

               );
    }

    public function getAllItems() {
        if (is_null($this->_allItems)) {
            $raw = parent::getAllItems();
            $data = array();
            foreach ($raw as $key => $val) {
                $data[$key] = $this->_prepareItemValue($key,$val);
            }
            // Do not add active ranges to items
            if(!($this instanceof Zolago_Solrsearch_Block_Faces_Price)) {
                foreach($this->_getActive() as $item) {
                    if(!isset($data[$item])) {
                        $data[$item] = $this->_prepareItemValue($item,0);
                    }
                }
            }
            $this->_allItems = $data;
        }
        return $this->_allItems;
    }

    public function getActiveItems() {
        $filterQuery = $this->getFilterQuery();
        if(isset($filterQuery[$this->getFacetKey()])) {
            return $filterQuery[$this->getFacetKey()];
        }
        return array();
    }


    public function getItems() {
        if(!$this->hasData("items")) {
            $hiddenItems = array();
            $items = $this->getAllItems();
            if($this->getFilterModel()) {
                $items =  $this->filterAndSortOptions(
                              $this->getAllItems(),
                              $this->getFilterModel(),
                              $hiddenItems
                          );
            }

            $this->setData("items", $items);
            $this->setData("hidden_items", $hiddenItems);
        }
        return $this->getData("items");
    }

	/**
	 * Return array of not selected field options for attribute
	 *
	 * @return array
	 */
    public function getHiddenItems() {
		/** @var Zolago_Catalog_Model_Category_Filter $filter */
		$filter = $this->getFilterModel();
        if($filter && $filter->getCanShowMore()
                && is_array($this->getData("hidden_items"))) {
            return $this->getData("hidden_items");
        }
        return array();
    }


    protected function _getActive() {
        if (is_null($this->_active)) {
            $active = $this->getActiveItems();
            $this->_active = is_array($active)? $active:array($active);
        }
        return $this->_active;
    }

    public function isItemActive($item) {
        return in_array((string)$item, $this->_getActive());
    }

    /**
     * @param string $item
     * @return string
     */
    public function getItemClass($item) {
        return $item['active'] ? "active" : "inactive";
    }

    /**
     * @param string $item
     * @return string
     */
    public function getItemName($item) {
        return "fq[" . $this->getAttributeCode() . "][]";
    }

    /**
     * @param string $item
     * @return string
     */
    public function getItemValue($item) {
        return $this->escapeHtml($item);
    }

    /**
     * @param string $item
     * @return string
     */
    public function getItemId($item) {
        return  $this->getFilterContainer()->getItemId($this->getAttributeCode(), $item);
    }


    /**
     *
     * @param type $key
     * @param type $value
     * @return type
     */
    public function getRemoveFacesUrl($key,$value)
    {
        return $this->getFilterContainer()->getRemoveFacesUrl($key, $value);
    }

    public function getRemoveAllFacesUrl($key) {
        return $this->getFilterContainer()->getRemoveAllFacesUrl($key);
    }

    public function processFinalParams(array $params) {
        return $this->getFilterContainer()->processFinalParams($params);
    }

    public function getRemoveFacesJson($key,$value)
    {
        return $this->getFilterContainer()->getRemoveFacesJson($key, $value);
    }

    public function getFacesUrl($params=array(), $paramss=NULL)
    {
        return $this->getFilterContainer()->getFacesUrl($params, $paramss);
    }

    public function getFacesJson($params=array(), $paramss=NULL)
    {
        return $this->getFilterContainer()->getFacesJson($params, $paramss);
    }

    public function getItemUrl($item, $param = array()) {
        /** @var $this Zolago_Solrsearch_Block_Faces_Abstract */
        $face_key = $this->getAttributeCode();
        if($this->isItemActive($item)) {
            $facetUrl = $this->getRemoveFacesUrl($face_key, $item);
        } else {
            if (empty($param)) {
                if ($face_key != 'price') {
                    $param = array('fq' => array($face_key => array($item)));
                } else {
                    $param = array('fq' => array($face_key => $item));
                }
            }
            $facetUrl = $this->getFacesUrl($param);
        }
        return $facetUrl;
    }

    public function getRemoveAllUrl($key) {
        $facetUrl = $this->getRemoveAllFacesUrl($key);
        return $facetUrl;
    }

    public function getItemJson($item) {
        $face_key = $this->getAttributeCode();
        if($this->isItemActive($item)) {
            $json = $this->getRemoveFacesJson($face_key, $item);
        } else {
            $json = $this->getFacesJson(array('fq' => array($face_key => $item)));
        }
        return $json;
    }

    public function isFilterActive() {
        return $this->getFilterContainer()->isFilterActive($this->getAttributeCode());
    }

    public function isFilterRolled() {
        if($this->getFilterModel()) {
            return $this->getFilterModel()->getIsRolled() && !$this->isFilterActive();
        }
        return false;
    }

    public function getAllOptions() {
        if(!$this->hasData("all_options"))
        {
            $source = $this->getAttributeSource($this->getAttributeCode());
            if($source) {
                // @todo - posprzątać to cudo
                if (method_exists($source, "setUseCustomOptions")) {
                    $source->setUseCustomOptions(true);
                    
                }
                //cache
                $key = 'block_faces_abstract_options_'.$this->getAttributeCode();
                if (!($optionsSerialize = $this->_getApp()->loadCache($key)) ||
                        !$this->_getApp()->useCache(self::CACHE_GROUP)) {

                    $options = $source->getAllOptions(true);
                    if ($this->_getApp()->useCache(self::CACHE_GROUP)) {
                        $this->_getApp()->saveCache(serialize($options),$key,array(self::CACHE_GROUP),Zolago_Common_Block_Page_Html_Head::BLOCK_CACHE_TTL);
                    }
                }
                if ($optionsSerialize) {
                    $options = unserialize($optionsSerialize);
                }
                $this->setData("all_options", $options);
            } else {
                $this->setData("all_options", array());
            }
        }
        return $this->getData("all_options");
    }


    /**
     * @param array $allItems
     * @param type $filter
     * @param array $hiddenItems
     * @return array
     */
    public function filterAndSortOptions(array $allItems, $filter, array &$hiddenItems) {

        if(!$this->getAllOptions()) {
            return $allItems;
        }
        $out = array();
        $allSourceOptions = $this->getAllOptions();
        $extraAdded = array();
        $labelAsValue = $filter->getCustomShowFlag(); // special for campaigns
        
        // Options are sorted via admin panel
        

        foreach($allSourceOptions as $option) {
            if ($labelAsValue && isset($option['value'])) {
                $label = $option['value']; // campaigns
            } else {
                $label = $option['label']; // other filters
            }
            $label = trim($label);
            // Option not in available result collection
            if(!isset($label) ||
                    !isset($allItems[$label])) {
                continue;
            }
            $count = $allItems[$label]['count']; 

            if($filter && $filter->getUseSpecifiedOptions()) {
                // Force show all items is filter is active and multiple
                $specifiedIds = $filter->getSpecifiedOptions();
                // Active single mode filter
                if(!$filter->getShowMultiple() && $this->isFilterActive()) {
                    if($allItems[$label]['active']) {
                        $out[$label] = $allItems[$label];
                        $count = $allItems[$label]['count'];
                        $this->_maxCount = ($count>$this->_maxCount)? $count: $this->_maxCount;
                        break;
                    }
                } else {
                    if(in_array($option['value'], $specifiedIds)) {
                        // Option specified - move to items
                        $out[$label] = $allItems[$label];
                        $this->_maxCount = ($count>$this->_maxCount)? $count: $this->_maxCount;
                    } elseif($this->isFilterActive() && $filter->getShowMultiple()
                           && $filter->getCanShowMore()) {
                        // Multiselect active - show all fileds, after specified fields
                        $extraAdded[$label] = $allItems[$label];
                        $this->_maxCount = ($count>$this->_maxCount)? $count: $this->_maxCount;
                    } elseif($this->isFilterActive() && $allItems[$label]['active']) {
                        // Add olny one item
                        $out[$label] = $allItems[$label];
                        $this->_maxCount = ($count>$this->_maxCount)? $count: $this->_maxCount;
                    } else {
                        // Option non specified move to hidden
                        $hiddenItems[$label] = $allItems[$label];
                        $this->_maxCountHidden = ($count>$this->_maxCountHidden)? $count: $this->_maxCountHidden;
                    }
                }
            } else {
                if($filter->getShowMultiple() || !$this->isFilterActive()) {
                    // No specified values - show all - if none active or filter is multiple
                    $out[$label] = $allItems[$label];
                    $this->_maxCount = ($count>$this->_maxCount)? $count: $this->_maxCount;
                }
                elseif($this->isFilterActive() && $allItems[$label]['active']) {
                    // if filter is single and item active - add only this one
                    $out[$label] = $allItems[$label];
                    $this->_maxCount = ($count>$this->_maxCount)? $count: $this->_maxCount;
                    break;
                }
            }
        }
        $this->_canShow = ($this->_maxCount>0)? true:false;
		$this->_canShowHidden = ($this->_maxCountHidden > 0) ? (bool)$this->getFilterModel()->getCanShowMore() : false;
        return $out+$extraAdded;

    }

    /**
     * @todo optymalize
     * @param string $code
     * @return Mage_Eav_Model_Entity_Attribute_Source_Interface | null
     */
    public function getAttributeSource($code) {
        $attribute = Mage::getSingleton('eav/config')->
                     getAttribute(Mage_Catalog_Model_Product::ENTITY, $code)->
                     setStoreId($this->getStoreId());
        return $attribute->getSource();
    }


    public function getStoreId() {
        return Mage::app()->getStore()->getId();
    }

    // Can show filter block
    public function getCanShow() {
        if (!$this->getCanShowItems()) {
            $model = $this->getFilterModel();
            if ($model && $model->getCanShowMore()) {
                return $this->getCanShowHidden();
            }
            return false;
        }
        return true;
    }
    // Can show visible items list
    public function getCanShowItems() {
        return $this->_getCanShow('_canShow');
    }
    // Can show hidden (show more)
    public function getCanShowHidden() {
        $this->getItems();
        return $this->_getCanShow('_canShowHidden');
    }
    // Can show item
    public function getCanShowItem($item) {
        return $item['count']>0 || $item['active'];
    }

    protected function _getCanShow($what) {
        if (is_null($this->$what)) {
            $this->getItems();
        }
        return $this->$what;
    }

    public function getSolrModel() {
        if(!$this->_solrModel) {
            $this->_solrModel = Mage::getModel('solrsearch/solr');
        }
        return $this->_solrModel;
    }

    public function getSolrData() {
        if(!$this->_solrData) {
            $this->_solrData = Mage::getSingleton('zolagosolrsearch/catalog_product_list')->getSolrData();
        }
        return $this->_solrData;
    }

    public function getFilterQuery()
    {
        if (!$this->_filterQuery) {
            $this->_filterQuery = $this->getSolrModel()->getStandardFilterQuery();
        }
        return $this->_filterQuery;
    }


    protected function _extractAttributeCode($facet) {
        return preg_replace("/_facet$/", "", $facet);
    }

    public function getSuperAttributeRelation() {
        $facetKey = $this->getFacetKey();
        //Zend_Debug::dump($facetKey);

        $solrData = $this->getSolrData();
        $facetFields = $solrData['facet_counts']['facet_fields'];

        $superAttributeFacet = isset($facetFields['super_attribute_' . $facetKey . '_color_facet_facet']) ? $facetFields['super_attribute_' . $facetKey . '_color_facet_facet'] : false;
        if (!$superAttributeFacet){
            return;
        }

        //krumo($superAttributeFacet);
        //krumo($this->getFilterQuery());

        $availableLabels = array();
        foreach($superAttributeFacet as $superAttributeFacetItem => $v){
            $data = explode("_", $superAttributeFacetItem);
            //Zend_Debug::dump($data);
            $availableLabels[$data[0]][] = $data[1];

        }
        //krumo($availableLabels);

        foreach ($this->getItems() as $item){
            //Zend_Debug::dump($item['item']);
        }




    }

    public function getFacetLabel($facetCode=null) {

        if(is_null($facetCode)) {
            $facetCode =  $this->getFacetKey();
        }
        $attributeCode = $this->_extractAttributeCode($facetCode);

        $facetLabelCache = Mage::app()->loadCache('solr_bridge_'.$facetCode.'_cache');

        if ( isset($facetLabelCache) && !empty($facetLabelCache) ) {
            return $facetLabelCache;
        } else {
            $entityType = Mage::getModel('eav/config')->getEntityType('catalog_product');
            $catalogProductEntityTypeId = $entityType->getEntityTypeId();

            $facetFieldsInfo = Mage::getResourceModel('eav/entity_attribute_collection')
                               ->setEntityTypeFilter($catalogProductEntityTypeId)
                               ->setCodeFilter(array($attributeCode))
                               ->addStoreLabel(Mage::app()->getStore()->getId());

            $facetLabel = '';
            foreach($facetFieldsInfo as $att) {
                if ($att->getAttributeCode() == $attributeCode) {
                    $facetLabel = $att->getStoreLabel();
                    Mage::app()->saveCache($facetLabel, 'solr_bridge_'.$facetCode.'_cache', array(), 60*60*24*360);
                    break;
                }
            }
            if ($attributeCode == 'category') {
                $facetLabel = $this->__('Category');
            }
            return $facetLabel;
        }
    }





}