<?php
class Zolago_SalesRule_Helper_Data extends Mage_SalesRule_Helper_Data {

	public function getActiveSalesRules() {
		$out = array();
		$rules = Mage::getResourceModel('salesrule/rule_collection')->load();

		foreach($rules as $rule) {
			if($rule->getIsActive()) {
				$out[] = $rule;
			}
		}
		return $out;
	}
}