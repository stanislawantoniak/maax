<?php
class Zolago_Catalog_Model_Resource_Vendor_Price_Detail_Collection 
	extends Mage_Catalog_Model_Resource_Product_Collection
{
  
	protected function _afterLoad() {
		parent::_afterLoad();
		$this->addChildProductsData();
	}
	
	public function addChildProductData() {
		
	}
   
}


