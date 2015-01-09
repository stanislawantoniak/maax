<?php
class Zolago_SalesRule_Helper_Data extends Mage_SalesRule_Helper_Data {

	public function getActiveSalesRules() {
		$rules = Mage::getResourceModel('salesrule/rule_collection')
			->addIsActiveFilter()
			->load();

		return $rules;
	}

	protected function getStoreId() {
		if (strlen($code = Mage::getSingleton('adminhtml/config_data')->getStore())) // store level
		{
			$store_id = Mage::getModel('core/store')->load($code)->getId();
		}
		elseif (strlen($code = Mage::getSingleton('adminhtml/config_data')->getWebsite())) // website level
		{
			$website_id = Mage::getModel('core/website')->load($code)->getId();
			$store_id = Mage::app()->getWebsite($website_id)->getDefaultStore()->getId();
		}
		else // default level
		{
			$store_id = 0;
		}

		return $store_id;
	}

	public function getUnusedCouponByRuleId($ruleId) {
		$coupon = Mage::getResourceModel('salesrule/coupon_collection')
			->addFieldToFilter('newsletter_sent', 0)
			->addFieldToFilter('rule_id',$ruleId)
			->setPageSize(1)
			->load()
			->getFirstItem();

		return $coupon->getType() == 1 ? $coupon : false;
	}

	public function getSalesRuleDesc($ruleId) {
		$rule = Mage::getResourceModel("salesrule/rule_collection")
			->addFieldToFilter('rule_id',$ruleId)
			->load()
			->getFirstItem();

		return $rule->getDescription();
	}
}