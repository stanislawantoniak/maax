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
		
		$categoty_id = $last['id'];
		$category = Mage::getModel('catalog/category')->load($categoty_id);
		if($this->getParentBlock()->getMode()==Zolago_Solrsearch_Block_Faces::MODE_CATEGORY){
			$facetUrl = $category->getUrl($category);			
		}
		else{
			
			$names = array();
			$ids   = array();
			
			$names[] = $last['name'];
			$parent_category_id = $last['id'];
			$ids[] = $last['id'];
			$children_category_ids = $category->getResource()->getChildren($category, true);
			if($children_category_ids){
				
				foreach($children_category_ids as $child_cat_id){
					
					$ids[] = $child_cat_id;
						
				}
			}
			// All category links need to have links to fresh categories
			// No appending to current params
			$params = $this->getRequest()->getParams();
			if(isset($params['fq']['category_id'])) unset($params['fq']['category_id']);
			if(isset($params['parent_cat_id'])) unset($params['parent_cat_id']);
			
			$facetUrl = $this->getFacesUrl(array('fq'=>array('category_id' => $ids), 'parent_cat_id' => $parent_category_id), $params);
			
			// if($this->isItemActive($item)){
				 // $facetUrl = $this->getRemoveFacesUrl("category", array($last['name']));
			// }
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
	
	/**
	 * Hide current category
	 * @param type $item
	 * @param type $count
	 * @return boolean
	 */
	
	public function getCanShowItem($item, $count) {
		return ($count > 0) ? true : false;
	}
	
	// public function getCanShow() {
		// if($this->getParentBlock()->getMode()==Zolago_Solrsearch_Block_Faces::MODE_CATEGORY){
			// $category = $this->getParentBlock()->getCurrentCategory();
			// $all = $this->getAllItems();
			// // One item with couurent cat
			// if(count($all)==1){
				// list($item, $count) = each($all);
				// $array = $this->pathToArray($item);
				// if($array){
					// $last = array_pop($array);
					// if(isset($last['id']) && $last['id']==$category->getId()){
						// return false;
					// }
				// }
			// }
		// }	
// 		
		// return parent::getCanShow();
	// }
	
}