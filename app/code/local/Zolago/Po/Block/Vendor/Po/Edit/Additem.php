<?php
class Zolago_Po_Block_Vendor_Po_Edit_Additem
	extends Zolago_Po_Block_Vendor_Po_Edit_Abstract
{

	/**
	 * @return Mage_Catalog_Model_Resource_Product_Collection
	 */
	public function getProductCollectionJson() {
		$collection = Mage::getResourceModel('catalog/product_collection');
		/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
		$collection->setStoreId($this->getPo()->getOrder()->getStoreId());
		$collection->addAttributeToSelect("name");
		$collection->addAttributeToSelect("price");
		$collection->addAttributeToSelect($this->getSkuAttribute()->getAttributeCode());
		$collection->addAttributeToFilter("udropship_vendor", $this->getVendor()->getId());
		$collection->addFieldToFilter("type_id", Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
		$collection->load();
		$collArray = array_values($collection->toArray());
		return Zend_Json::encode($collArray);
	}
	
}
