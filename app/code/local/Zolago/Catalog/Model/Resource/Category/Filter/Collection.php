<?php

class Zolago_Catalog_Model_Resource_Category_Filter_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
  
    protected function _construct() {
        parent::_construct();
        $this->_init('zolagocatalog/category_filter');
    }

	/**
	 * @return Zolago_Catalog_Model_Resource_Category_Filter_Collection
	 */
	public function joinAttributeCode() {
		$this->getSelect()->join(
				array("eav_attribute"=>  $this->getTable("eav/attribute")),
				"eav_attribute.attribute_id=main_table.attribute_id",
				array("attribute_code")
		);
		return $this;
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
	
	protected function _afterLoad() {
		foreach($this->_items as $item){
			$item->getResource()->unserializeFields($item);
		}
		return parent::_afterLoad();
	}
	
}
