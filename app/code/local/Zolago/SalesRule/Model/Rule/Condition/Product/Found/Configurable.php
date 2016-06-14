<?php

/**
 * Modification: validation only for configurable products
 *
 * Class Zolago_SalesRule_Model_Rule_Condition_Product_Found_Configurable
 */
class Zolago_SalesRule_Model_Rule_Condition_Product_Found_Configurable extends Mage_SalesRule_Model_Rule_Condition_Product_Found
{
	public function __construct()
	{
		parent::__construct();
		$this->setType('zolagosalesrule/rule_condition_product_found_configurable');
	}

	public function asHtml()
	{
		$html = $this->getTypeElement()->getHtml() . Mage::helper('salesrule')->__("If an CONFIGURABLE item is %s in the cart with %s of these conditions true:", $this->getValueElement()->getHtml(), $this->getAggregatorElement()->getHtml());
		if ($this->getId() != '1') {
			$html.= $this->getRemoveLinkHtml();
		}
		return $html;
	}

	public function getNewChildSelectOptions()
	{
		$productCondition = Mage::getModel('zolagosalesrule/rule_condition_product_configurable');
		$productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
		$pAttributes = array();
		foreach ($productAttributes as $code=>$label) {
			if (!(strpos($code, 'quote_item_')===0)) {
				$pAttributes[] = array('value'=>'zolagosalesrule/rule_condition_product_configurable|'.$code, 'label'=>$label);
			}
		}

		$conditions = array(
			array('label'=>Mage::helper('catalog')->__('Conditions Combination'), 'value'=>'salesrule/rule_condition_product_combine'),
			array('label'=>Mage::helper('catalog')->__('Product Attribute'), 'value'=>$pAttributes),
		);
		return $conditions;
	}

	/**
	 * validate
	 *
	 * @param Varien_Object $object Quote
	 * @return boolean
	 */
	public function validate(Varien_Object $object)
	{
		$all = $this->getAggregator()==='all';
		$true = (bool)$this->getValue();
		$found = false;

		// Modification: only for configurable products
		$allItems = $object->getAllItems() ? $object->getAllItems() : array($object);
		$configurableItems = array();
		foreach ($allItems as $item) {
			/** @var Zolago_Catalog_Model_Product $product */
			$product = $item->getProduct();
			if ($product->getId() && $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
				$configurableItems[] = $item;
			}
		}

		foreach ($configurableItems as $item) {
			$found = $all;
			foreach ($this->getConditions() as $cond) {
				$validated = $cond->validate($item);
				if (($all && !$validated) || (!$all && $validated)) {
					$found = $validated;
					break;
				}
			}
			if (($found && $true) || (!$true && $found)) {
				break;
			}
		}
		// found an item and we're looking for existing one
		if ($found && $true) {
			return true;
		}
		// not found and we're making sure it doesn't exist
		elseif (!$found && !$true) {
			return true;
		}
		return false;
	}
}
