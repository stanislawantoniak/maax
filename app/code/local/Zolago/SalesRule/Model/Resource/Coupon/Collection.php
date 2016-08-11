<?php

/**
 * Class Zolago_SalesRule_Model_Resource_Coupon_Collection
 */
class Zolago_SalesRule_Model_Resource_Coupon_Collection extends Mage_SalesRule_Model_Resource_Coupon_Collection {


	/**
	 * Add filter to collection by customer_id
	 * This mean all coupons belongs to customer
	 *
	 * @param $customerId
	 * @return $this
	 */
	public function addCustomerIdFilter($customerId) {
		$this->addFieldToFilter('main_table.customer_id', (int)$customerId);
		return $this;
	}

	/**
	 * Add info about how many times coupon was used by customer
	 *
	 * @return $this
	 */
	public function addCustomerTimesUsedInfo() {
		$valueExpr = $this->getSelect()->getAdapter()
			->getCheckSql("tCouponUsage.times_used > 0", "tCouponUsage.times_used", "0");
		$this->getSelect()->joinLeft(
			array('tCouponUsage' => $this->getTable('salesrule/coupon_usage')),
			"main_table.coupon_id = tCouponUsage.coupon_id AND main_table.customer_id = tCouponUsage.customer_id",
			array('customer_times_used' => $valueExpr)
		);
		return $this;
	}
}
