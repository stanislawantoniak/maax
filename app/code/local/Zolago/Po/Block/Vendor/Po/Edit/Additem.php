<?php
class Zolago_Po_Block_Vendor_Po_Edit_Additem
	extends Zolago_Po_Block_Vendor_Po_Edit_Abstract
{

	public function getProductCollectionJson() {
		$collection = Mage::getResourceModel('catalog/product_collection');
		/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
		$collection->addAttributeToSelect("name");
		$collection->addAttributeToSelect("sku");
		$collection->addAttributeToSelect("entity_id");
//		$collArray = array();
//		foreach($collection as $product){
//			$collArray[] = array(
//				"id" => $product->getId(),
//				"text" => $product->getName(),
//			);
//		}
		
		$collection->load();
		$collArray = array_values($collection->toArray());
		return Zend_Json::encode($collArray);
	}
	
}
