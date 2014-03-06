<?php
class Zolago_Adminhtml_Block_Catalog_Category_Tree extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
	public function getEditUrl() {
		if($this->getCategoryId()){
			return $this->getUrl("*/catalog_category/edit", array(
				'_current'	=>	true, 
				'store'		=>	null, 
				'_query'	=>	false, 
				'id'		=>	$this->getCategoryId(), 
				'parent'	=>	null
			));
		}
		return parent::getEditUrl();
	}
}
