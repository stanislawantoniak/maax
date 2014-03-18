<?php

class Zolago_Solrsearch_Block_Faces_Category extends Zolago_Solrsearch_Block_Faces_Abstract
{
	public function __construct()
	{
		$this->setTemplate('zolagosolrsearch/standard/searchfaces/category.phtml');
	}

	
	public function getParsedCategories() {
		return $this->getParentBlock()->parseCategoryPathFacet($this->getAllItems());
	}
	
	public function getFacetLabel($facetCode=null) {
		return Mage::helper('catalog')->__("Category");
	}
	
	public function getItemUrl($item) {
		
		$array = $this->pathToArray($item);
		$last = array_pop($array);
		$facetUrl = $this->getFacesUrl(array('fq'=>array('category'=>array($last['name']), 'category_id' => array($last['id']))));
		if($this->isItemActive($item)){
			 $facetUrl = $this->getRemoveFacesUrl("category", array($last['name']));
		}
		return $facetUrl;
	}
	
	public function isItemActive($item) {
		$filterQuery = $this->getFilterQuery();
		if(!isset($filterQuery["category_id"])){
			return false;
		}
		
		$array = $this->pathToArray($item);
		
		foreach($array as $_item){
			if(in_array($_item['id'], $filterQuery["category_id"])){
				return true;
			}
		}
		return false;
	}
	
	public function getItemText($item) {
		$array = $this->pathToArray($item);
		$last = array_pop($array);
		return $last['name'];
	}
	
	public function pathToArray($path) {
    	$chunks = explode('/', $path);
    	$result = array();
    	for ($i = 0; $i < sizeof($chunks) - 1; $i+=2)
    	{
    		$result[] = array('id' => $chunks[($i+1)], 'name' => $chunks[$i]);
    	}

    	return $result;
    }
	
}