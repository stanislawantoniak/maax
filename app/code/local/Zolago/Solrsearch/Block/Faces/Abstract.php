<?php
/**
 * @method Zolago_Catalog_Model_Category_Filter getFilterModel() Description
 */
abstract class Zolago_Solrsearch_Block_Faces_Abstract extends Mage_Core_Block_Template
{
	protected $_solrData;
	protected $_filterQuery;
	protected $_solrModel;

	public function __construct()
	{
		$this->setTemplate('zolagosolrsearch/standard/searchfaces/enum.phtml');
	}
	
	public function getAllItems() {
		$data = parent::getAllItems();
		foreach($this->getActiveItems() as $item){
			if(!isset($data[$item])){
				$data[$item] = 0;
			}
		}
		return $data;
	}
	
	public function getActiveItems() {
		$filterQuery = $this->getFilterQuery();
		if(isset($filterQuery[$this->getFacetKey()])){
			return $filterQuery[$this->getFacetKey()];
		}
		return array();
	}


	public function getItems() {
		if(!$this->hasData("items")){
			$items = $this->getAllItems();
			$hiddenItems = array();
			$filterModel = $this->getFilterModel();
			if($filterModel && $filterModel->getUseSpecifiedOptions()){
				$items =  $this->filterOptions($items, $filterModel->getSpecifiedOptions(), $hiddenItems);
			}
			ksort($items);
			ksort($hiddenItems);
			$this->setData("items", $items);
			$this->setData("hidden_items", $hiddenItems);
		}
		return $this->getData("items");
	}
	
	public function getHiddenItems() {
		if($this->getFilterModel() && $this->getFilterModel()->getCanShowMore() 
				&& is_array($this->getData("hidden_items"))){
			return $this->getData("hidden_items");
		}
		return array();
	}

	public function isItemActive($item) {
		$filterQuery = $this->getFilterQuery();
		if (isset($filterQuery[$this->getFacetKey()]) && in_array($item, $filterQuery[$this->getFacetKey()])) {
			return true;
		}
		return false;
	}
	
	public function getItemClass($item) {
		return $this->isItemActive($item) ? "active" : "inactive";
	}
	
	public function getRemoveFacesUrl($key,$value)
    {
		return $this->getFilterContainer()->getRemoveFacesUrl($key, $value);
    }

	public function getFacesUrl($params=array())
    {
		return $this->getFilterContainer()->getFacesUrl($params);
    }
	
	public function getItemUrl($item) {
		$face_key = $this->getAttributeCode();
		$facetUrl = $this->getFacesUrl(array('fq' => array($face_key => $item)));
		if($this->isItemActive($item)){
			 $facetUrl = $this->getRemoveFacesUrl($face_key, $item);
		}
		return $facetUrl;
	}
	

	
	public function filterOptions(array $allItems, array $specifiedIds, array &$hiddenItems) {
		$attrCode = $this->getAttributeCode();
		$source = $this->getAttributeSource($attrCode);
		$out = array();
		
		if(!$source){
			return $allItems;
		}
		
		$allSourceOptions = $source->getAllOptions(false);
		
		foreach($allSourceOptions as $option){
			if(!isset($allItems[$option['label']])){
				continue;
			}
			
			// Force add active items
			if($this->isItemActive($option['label'])){
				$out[$option['label']] = $allItems[$option['label']];
				continue;
			}
			
			// Option specified - move to items
			if(in_array($option['value'], $specifiedIds)){
				$out[$option['label']] = $allItems[$option['label']];
		    // Option non specified move to hidden
			}else{
				$hiddenItems[$option['label']] = $allItems[$option['label']];
			}
		}
		return $out;
		
	}
	
	/**
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
		return $this->_getCanShow($this->getAllItems());
	}
	// Can show visible items list
	public function getCanShowItems() {
		return $this->_getCanShow($this->getItems());
	}
	// Can show hidden (show more)
	public function getCanShowHidden() {
		$this->getItems();
		return $this->_getCanShow($this->getHiddenItems());
	}
	// Can show item
	public function getCanShowItem($item, $count) {
		return $count>0 /*|| $this->isItemActive($item)*/;
	}
	
	protected function _getCanShow(array $what) {
		return count($what) ? max($what)>0 : false;
	}
	
	public function getSolrModel() {
		if(!$this->_solrModel){
			$this->_solrModel = parent::getSolrModel();
		}
		return $this->_solrModel;
	}
	
	public function getSolrData() {
		if(!$this->_solrData){
			$this->_solrData = parent::getSolrData();
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
	

	public function getFacetLabel($facetCode=null){

		if(is_null($facetCode)){
			$facetCode =  $this->getFacetKey();
		}
    	$attributeCode = $this->_extractAttributeCode($facetCode);

    	$facetLabelCache = Mage::app()->loadCache('solr_bridge_'.$facetCode.'_cache');

    	if ( isset($facetLabelCache) && !empty($facetLabelCache) ) {
    		return $facetLabelCache;
    	}else {
    		$entityType = Mage::getModel('eav/config')->getEntityType('catalog_product');
			$catalogProductEntityTypeId = $entityType->getEntityTypeId();

			$facetFieldsInfo = Mage::getResourceModel('eav/entity_attribute_collection')
			->setEntityTypeFilter($catalogProductEntityTypeId)
			->setCodeFilter(array($attributeCode))
			->addStoreLabel(Mage::app()->getStore()->getId());

			$facetLabel = '';
			foreach($facetFieldsInfo as $att){
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