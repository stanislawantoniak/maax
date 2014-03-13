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
	
	/**
	 * @return array(attrCode=>blockObject, ...)
	 */
	public function getFilterBlocks() {
		
    	$solrData = $this->getSolrData();
    	$priceFieldName = Mage::helper('solrsearch')->getPriceFieldName();
    	$facetFileds = array();
		$blocks = array();
		
    	if (isset($solrData['facet_counts']['facet_fields']) && is_array($solrData['facet_counts']['facet_fields'])) {
    		$facetFileds = $solrData['facet_counts']['facet_fields'];
    	}

    	//Ignore the price_decimal
    	if (isset($facetFileds[$priceFieldName])) {
    		unset($facetFileds[$priceFieldName]);
    	}
		
		
		
		// Category mode
		foreach($facetFileds as $key=>$data){
				
			$attrCode = $this->_extractAttributeCode($key);
			
			if($this->getMode()==self::MODE_CATEGORY){
				$filter = $this->getFilterByAttribute($attrCode);
				if(!$filter || !$filter->getId()){
					continue;// Skip attribs with no custom filter
				}
				
				// Is multiple values
				if($filter->getShowMultiple()){
					$data = $this->_prepareMultiValues($facetFileds, $key);
				}
				
				$renderer = $this->getDefaultRenderer();
				if($filter->getCustomRender()){
					$renderer = $filter->getCustomRender();
				}
				
				$block = $this->getLayout()->createBlock($renderer);
				/* @var $block Zolago_Solrsearch_Block_Faces_Abstract */
				
			
				if(! ($block instanceof  Zolago_Solrsearch_Block_Faces_Abstract)){
					throw new Exception("Unknow block type $renderer");
				}
				
				$block->setFilterModel($filter);
			}else{
				// Search mode - unknow category filters
				$block= $this->getLayout()->createBlock(
						$this->getDefaultRenderer()
				);
			}
			
			$block->setAllItems($data);
			$block->setAttributeCode($attrCode);
			$block->setFacetKey($key);
			$blocks[] = $block;
		}
		
		foreach($blocks as $block){
			$block->setFilterContainer($this);
			$block->setSolrModel($this->solrModel);
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
		if($this->getCurrentCategory() && !Mage::registry('current_product')){
			return self::MODE_CATEGORY;
		}
		return self::MODE_SEARCH;
	}

	/**
	 * @return Mage_Catalog_Model_Category
	 */
	public function getCurrentCategory() {
		return Mage::registry('current_category');
	}
	
	/**
	 * @return Zolago_Catalog_Model_Resource_Category_Filter_Collection
	 */
	public function getFilterCollection() {
		if(!$this->hasData("filter_collection")){
			$collection = Mage::getResourceModel("zolagocatalog/category_filter_collection");
			/* @var $collection Zolago_Catalog_Model_Resource_Category_Filter_Collection */
			$collection->joinAttributeCode();
			if($this->getCurrentCategory()){
				$collection->addCategoryFilter($this->getCurrentCategory());
			}
			$this->setData("filter_collection", $collection);
			
		}
		return $this->getData("filter_collection");
	}
	
	
	public function getFilterByAttribute($attrCode) {
		if(!$this->hasData("filter_by_attribute")){
			$attributeByCode = array();
			foreach($this->getFilterCollection() as $filter){
				$attributeByCode[$filter->getAttributeCode()] = $filter;
			}
			$this->setData("filter_by_attribute", $attributeByCode);
		}
		return $this->getData("filter_by_attribute", $attrCode);
	}
	
	


	protected function _prepareMultiValues(&$facetData, $facetkey) {
		
		$queryText = Mage::helper('solrsearch')->getParam('q');

		$key = Mage::helper('solrsearch')->getKeywordCachedKey($queryText);

		$originalSolrData = Mage::getSingleton('core/session')->getOriginSolrFacetData();

		if (isset($originalSolrData) && isset($originalSolrData[$key])) {

			if (isset($originalSolrData[$key]['facet_counts']['facet_fields'][$facetkey]) && !empty($originalSolrData[$key]['facet_counts']['facet_fields'][$facetkey])) {
				$facetData[$facetkey] = $originalSolrData[$key]['facet_counts']['facet_fields'][$facetkey];
			}

		}
		//Update original facet data
		$originalSolrData[$key]['facet_counts']['facet_fields'] = $facetData;
		Mage::getSingleton('core/session')->setOriginSolrFacetData($originalSolrData);
		return $facetData[$facetkey];
	}
}