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
	
	
	protected function _checkFilterDepedncy($filter) {

		if(!($parentAttributeId = $filter->getParentAttributeId())){
			return true;
		}
		if($parentAttributeCode = $this->getAttributeCodeById($parentAttributeId)){
			return $this->_isFilterActive($parentAttributeCode);
		}
		return false;
	}
	
	protected function _isFilterActive($attrCode) {
		$filterQuery = $this->getFilterQuery();
		if (isset($filterQuery[$attrCode."_facet"])) {
			return true;
		}
		return false;
	}
	
	public function getDependAttributes($attributeCode) {
		$depends = array();
		$attributeId = $this->getAttributeIdByCode($attributeCode);
		foreach($this->getFilterCollection() as $fiter){
			if($fiter->getParentAttributeId()==$attributeId){
				$depends[] = $this->getAttributeCodeById($fiter->getAttributeId());
			}
		}
		return $depends;
	}
	
  public function getRemoveFacesUrl($key,$value)
    {
        $paramss = $this->getRequest()->getParams();

        $finalParams = $paramss;

		if (!(is_array($key) && is_array($value) && count($key) == count($value))){
			$key = array($key);
			$value = array($value);
		}
		

		foreach ($key as $item)
		{
			if (isset($finalParams['fq'][$item]) && !is_array($finalParams['fq'][$item]) && !empty($finalParams['fq'][$item])) {
				unset($finalParams['fq'][$item]);
				if ($item == 'category' && isset($finalParams['fq'][$item.'_id'])) {
					unset($finalParams['fq'][$item.'_id']);
				}
			}else if (isset($finalParams['fq'][$item]) && is_array($finalParams['fq'][$item]) && count($finalParams['fq'][$item]) > 0) {
				foreach ($finalParams['fq'][$item] as $k=>$v) {
					if ($v == $value) {
						unset($finalParams['fq'][$item][$k]);
						if ($item == 'category' && isset($finalParams['fq'][$item.'_id']) && isset($finalParams['fq'][$item.'_id'][$k])) {
							unset($finalParams['fq'][$item.'_id'][$k]);
						}
					}
				}
			}			

			// Unset all depended fileds
			foreach($this->getDependAttributes($item) as $depend){
				if(isset($finalParams['fq'][$depend])){
					unset($finalParams['fq'][$depend]);
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
				
				// Skip attribs with no custom filter
				if(!$filter || !$filter->getId()){
					continue;
				}
				
				// Check is filter depended - if not - skip
				if($filter->getParentAttributeId() && !$this->_checkFilterDepedncy($filter)){
					continue;
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
	
	public function getAttributeCodeById($attrCode) {
		if(!$this->hasData("attribute_code_by_id")){
			$attributeCodeById = array();
			foreach($this->getFilterCollection() as $filter){
				$attributeCodeById[$filter->getAttributeId()] = $filter->getAttributeCode();
			}
			$this->setData("attribute_code_by_id", $attributeCodeById);
		}
		return $this->getData("attribute_code_by_id", $attrCode);
	}
	
	public function getAttributeIdByCode($attrCode) {
		if(!$this->hasData("attribute_id_by_code")){
			$attributeCodeById = array();
			foreach($this->getFilterCollection() as $filter){
				$attributeCodeById[$filter->getAttributeCode()] = $filter->getAttributeId();
			}
			$this->setData("attribute_id_by_code", $attributeCodeById);
		}
		return $this->getData("attribute_id_by_code", $attrCode);
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