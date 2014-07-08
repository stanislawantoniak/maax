<?php
/**
 * Class Zolago_Catalog_Helper_Category
 */
class Zolago_Catalog_Helper_Category extends Mage_Core_Helper_Abstract
{
	private $all_categories = NULL;
	
	/**
	 * @return array Array of categories in for of id => path
	 */	
	public function getPathArray(){
		
		$categories = Mage::getModel('catalog/category')
                ->getCollection()
                ->addAttributeToSelect('id, path')
                ->addIsActiveFilter();
				
		$all_categories = array();
		foreach ($categories as $c) {
		   $all_categories[$c->getId()] = $c->getPath();
		}
		
		$this->all_categories = $all_categories;
		
		return $all_categories;
	}
	
	/**
	 * @param int $parent_cat_id 
	 * @param array $all_categories
	 * 
	 * @return array
	 */
	public function getChildrenIds($parent_cat_id, $all_categories = NULL){
		
		$children_ids = array();
		
		if(!$all_categories){
			$all_categories = $this->all_categories;
		}
		
		if($all_categories){
			
			foreach($all_categories as $cat_id => $cat_path){
					
				if (strpos($cat_path, '/' . $parent_cat_id . '/') !== FALSE){
					$children_ids[] = $cat_id;
				}
			}
		}
		
		return $children_ids;
	}
}
