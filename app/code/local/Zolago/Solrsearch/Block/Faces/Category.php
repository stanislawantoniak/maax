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
	
}