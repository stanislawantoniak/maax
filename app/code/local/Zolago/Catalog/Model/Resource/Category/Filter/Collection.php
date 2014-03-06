<?php

class Zolago_Catalog_Model_Resource_Category_Filter_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
  
    protected function _construct() {
        parent::_construct();
        $this->_init('zolagocatalog/category_filter');
    }

	/**
	 * @param Mage_Catalog_Model_Category|int|array $category
	 * @return Zolago_Catalog_Model_Resource_Category_Filter_Collection
	 */
	public function	addCategoryFilter($category){
		if($category instanceof Mage_Catalog_Model_Category){
			$category = $category->getId();
		}
		if(!is_array($category)){
			$category = array($category);
		}
		$this->addFieldToFilter("category_id", array("in" => $category));
		return $this;
	}
	
}
