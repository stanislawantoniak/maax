<?php

/**
 * Modification: validation only for configurable products
 * 
 * Class Zolago_SalesRule_Model_Rule_Condition_Product_Configurable
 */
class Zolago_SalesRule_Model_Rule_Condition_Product_Configurable extends Mage_SalesRule_Model_Rule_Condition_Product {

	public function validate(Varien_Object $object) {

		/** @var Mage_Catalog_Model_Product $product */
		$product = $object->getProduct();
		if (!($product instanceof Mage_Catalog_Model_Product)) {
			$product = Mage::getModel('catalog/product')->load($object->getProductId());
		}

		$product
			->setQuoteItemQty($object->getQty())
			->setQuoteItemPrice($object->getPrice())// possible bug: need to use $object->getBasePrice()
			->setQuoteItemRowTotal($object->getBaseRowTotal());

		/** @var Zolago_SalesRule_Helper_Data $helper */
		$helper = Mage::helper("zolagosalesrule");
		$helper->copySalesRuleAttrToQuoteItem($product, $object);


		$valid = Mage_Rule_Model_Condition_Product_Abstract::validate($product);
		// Only for configurable
		/*
        if (!$valid && $product->getTypeId() == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
            $children = $object->getChildren();
            $valid = $children && $this->validate($children[0]);
        }
		*/

		return $valid;
	}
}
