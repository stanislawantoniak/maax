<?php

class Zolago_Newsletter_Model_Resource_Subscriber_Collection extends Mage_Newsletter_Model_Resource_Subscriber_Collection
{

	public function showCoupons()
	{

		$this->getSelect()
			->joinLeft(array('coupon_id_table'=>'salesrule_coupon'),
				"main_table.coupon_id = coupon_id_table.coupon_id",
				"coupon_id_table.code AS coupon_code"
			);

		return $this;
	}
}
