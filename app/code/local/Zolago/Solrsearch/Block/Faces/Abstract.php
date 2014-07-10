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
			$hiddenItems = array();
			$items = $this->getAllItems();
			
			if($this->getFilterModel()){
				$items =  $this->filterAndSortOptions(
						$this->getAllItems(), 
						$this->getFilterModel(), 
						$hiddenItems
				);
			}
			
			//ksort($items);
			//ksort($hiddenItems);
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
		if (isset($filterQuery[$this->getFacetKey()])) {
			if(is_array($filterQuery[$this->getFacetKey()])){
				return in_array($item, $filterQuery[$this->getFacetKey()]);
			}
			return trim($filterQuery[$this->getFacetKey()])==trim($item);
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
	
	public function getItemUrl($item) {
		$face_key = $this->getAttributeCode();
		$facetUrl = $this->getFacesUrl(array('fq' => array($face_key => $item)));
		if($this->isItemActive($item)){
			 $facetUrl = $this->getRemoveFacesUrl($face_key, $item);
		}
		return $facetUrl;
	}
	
	public function getItemJson($item) {
		$face_key = $this->getAttributeCode();
		if($this->isItemActive($item)){
			$json = $this->getRemoveFacesJson($face_key, $item);
		}else{
			$json = $this->getFacesJson(array('fq' => array($face_key => $item)));
		}
		return $json;
	}
	
	public function isFilterActive() {
		return $this->getFilterContainer()->isFilterActive($this->getAttributeCode());
	}
	
	public function isFilterRolled() {
		if($this->getFilterModel()){
			return $this->getFilterModel()->getIsRolled() && !$this->isFilterActive();
		}
		return false;
	}
	
	public function getAllOptions() {
		if(!$this->hasData("all_options"))
		{
			$source = $this->getAttributeSource($this->getAttributeCode());
			if($source){
				$this->setData("all_options", $source->getAllOptions(true));
			}else{
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
		
		if(!$this->getAllOptions()){
			return $allItems;
		}
		
		$out = array();
		$allSourceOptions = $this->getAllOptions();
		$extraAdded = array();
		// Options are sorted via admin panel
		
		foreach($allSourceOptions as $option){
			// Option not in available result colleciotn
			if(!isset($allItems[$option['label']])){
				continue;
			}
			
			if($filter && $filter->getUseSpecifiedOptions()){
				// Force show all items is filter is active and multiple
				$specifiedIds = $filter->getSpecifiedOptions();
				
				// Active single mode filter
				if(!$filter->getShowMultiple() && $this->isFilterActive()){
					if($this->isItemActive ($option['label'])){
						$out[$option['label']] = $allItems[$option['label']];
						break;
					}
					continue;
				}
				
				if(in_array($option['value'], $specifiedIds)){
					// Option specified - move to items
					$out[$option['label']] = $allItems[$option['label']];
				}elseif($this->isFilterActive() && $filter->getShowMultiple() 
					&& $filter->getCanShowMore()){
					// Multiselect active - show all fileds, after specified fields
					$extraAdded[$option['label']] = $allItems[$option['label']];
				}elseif($this->isFilterActive() && $this->isItemActive ($option['label'])){
					// Add olny one item
					$out[$option['label']] = $allItems[$option['label']];
				}
				else{
					// Option non specified move to hidden
					$hiddenItems[$option['label']] = $allItems[$option['label']];
				}
			}else{
				if($filter->getShowMultiple() || !$this->isFilterActive()){
					// No specified values - show all - if none active or filter is multiple
					$out[$option['label']] = $allItems[$option['label']];
				}elseif($this->isFilterActive() && $this->isItemActive ($option['label'])){
					// if filter is single and item active - add only this one
					$out[$option['label']] = $allItems[$option['label']];
					break;
				}
			}
		}
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
		if($this->getFilterModel()){
			// Has visible items
			if($this->getCanShowItems()){
				return true;
			}
			// Have some hiddne items
			if($this->getFilterModel()->getCanShowMore()){
				return $this->getCanShowHidden();
			}
		}
		// No filter - show items if have
		return $this->getCanShowItems();
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
		return $count>0 || $this->isItemActive($item);
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