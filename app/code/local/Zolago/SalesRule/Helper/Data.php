<?php
class Zolago_SalesRule_Helper_Data extends Mage_SalesRule_Helper_Data {

	public function getActiveSalesRules() {
		$rules = Mage::getResourceModel('salesrule/rule_collection')
			->addIsActiveFilter()
			->addFieldToFilter('coupon_type',2)
			->addFieldToFilter('use_auto_generation',1)
			->load();

		return $rules;
	}

	protected function getStoreId() {
		return Mage::app()->getStore()->getId();
	}

	/**
	 * @param Mage_SalesRule_Model_Rule $rule
	 * @return bool|Mage_SalesRule_Model_Coupon
	 */
	public function getUnusedCouponByRule($rule) {
		if(!$rule->getIsActive()) {
			return false;
		}

		/** @var Mage_SalesRule_Model_Coupon $coupon */
		$coupon = Mage::getResourceModel('salesrule/coupon_collection')
			->addFieldToFilter('newsletter_sent', 0)
			->addFieldToFilter('rule_id',$rule->getId())
			->setPageSize(1)
			->load()
			->getFirstItem();

		// $coupon type == 1 equals to auto-generated coupons
		if($coupon->getType() == 1) {
			return $coupon;
		} else {
			//Mage::log("All coupon codes for newsletter thank you emails are used or sales rule is misconfigured",null,"newsletter.log");
			return false;
		}
	}

    public static function analyzeCouponByCustomerRequest($code)
    {
        $error = '';
        if (empty($code)) {
            return;
        }
        $couponU = Mage::getModel('salesrule/coupon/usage');
        $couponU->load($code, 'code');
        if ($couponU->getId()) {
            $timesUsed = $couponU->getTimesUsed();
            if ($timesUsed > 0) {
                return Mage::helper('zolagomodago')->__('The coupon code has already been used.');
            }
        }

        //check if coupon expired
        $couponM = Mage::getModel('salesrule/coupon');
        $couponM->load($code, 'code');

        if ($couponM->getId()) {
            $localeTime = Mage::getModel('core/date')->timestamp(time());
            $localeTimeF = date("Y-m-d H:i", $localeTime);
            if ($couponM->getData('expiration_date') <= $localeTimeF) {
                return Mage::helper('zolagomodago')->__('The coupon is expired');
            }

        }

        return $error;
    }
}