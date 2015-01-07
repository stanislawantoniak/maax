<?php

class Zolago_SalesRule_Model_ActiveRules {
	public function toOptionArray() {
		/** @var Zolago_SalesRule_Helper_Data $helper */
		$helper = Mage::helper("zolagosalesrule");
		$rules = $helper->getActiveSalesRules();
		$out = array(
			array(
				'value' => '0',
				'label' => Mage::helper('core')->__("Off")
			)
		);
		foreach($rules as $rule) {
			$out[] = array(
				'value' => $rule->getId(),
				'label' => $rule->getName()
			);
		}
		return $out;
	}

}