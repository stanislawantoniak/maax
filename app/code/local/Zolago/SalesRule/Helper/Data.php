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
        $customerSession = Mage::getSingleton('customer/session');
        $groupId = 0;
        if($customerSession->isLoggedIn()){
            $groupId    = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }

        /* @var $couponCollection Mage_SalesRule_Model_Resource_Coupon_Collection */
        $couponCollection = Mage::getModel('salesrule/coupon')
            ->getCollection();
        $couponCollection
            ->getSelect()
            ->join(array('salesrule_website' => 'salesrule_website'),
                'salesrule_website.rule_id = main_table.rule_id',
                array('website_id'))
            ->join(array('salesrule_customer_group' => 'salesrule_customer_group'),
                'salesrule_customer_group.rule_id = main_table.rule_id',
                array('customer_group_id'))
            ->join(array('salesrule' => 'salesrule'),
                'salesrule.rule_id = main_table.rule_id',
                array('description', 'from_date', 'to_date', 'uses_per_customer', 'uses_per_coupon'));
        $couponCollection->addFieldToFilter('customer_group_id', $groupId);
        $couponCollection->addFieldToFilter('salesrule.is_active', 1);
        $couponCollection->addFieldToFilter('code', $code);
        $couponCollection->addFieldToFilter('website_id', Mage::app()->getWebsite()->getId());
        $couponM = $couponCollection->getFirstItem();

        $salesRuleId = $couponM->getRuleId();
        $couponId = $couponM->getId();

        if (empty($couponId)) {
            return;
        }
        //check if coupon expired
        $localeTime = Mage::getModel('core/date')->timestamp(time());
        $localeTimeF = date("Y-m-d H:i", $localeTime);
        $expirationDate = $couponM->getData('expiration_date');
        if (!empty($expirationDate) && $expirationDate <= $localeTimeF) {
            return Mage::helper('zolagomodago')->__('The coupon is expired');
        }

        //coupon usage
        $couponUsage = Mage::getModel('salesrule/coupon/usage');
        $couponUsage->load($code, 'code');

        if ($couponUsage->getId()) {
            //check uses_per_coupon
            $usesPerCoupon = $couponM->getData('uses_per_coupon');
            $timesUsed = $couponUsage->getTimesUsed();

            if ($timesUsed >= (int)$usesPerCoupon) {
                return Mage::helper('zolagomodago')->__('The coupon code has already been used.');
            }


            //check usage_per_customer
            /**
             * SELECT sco.code, scu.customer_id, scu.times_used FROM salesrule_coupon sco
             * INNER JOIN salesrule_customer scu ON sco.rule_id = scu.rule_id
             * GROUP BY scu.customer_id;
             */

            if ($customerSession->isLoggedIn()) {
                $customer = $customerSession->getCustomer();
                $customerId = $customer->getId();

                if (!empty($customerId)) {
                    /* @var $couponUsageByCustomerM Mage_SalesRule_Model_Rule_Customer */
                    $couponUsageByCustomerM = Mage::getModel('salesrule/rule_customer');
                    $couponUsageByCustomerM = $couponUsageByCustomerM->loadByCustomerRule($customerId, $salesRuleId);
                    $couponUsageByCustomerMId = $couponUsageByCustomerM->getId();

                    if (!empty($couponUsageByCustomerMId)) {
                        $usagePerCustomer = $couponM->getData('usage_per_customer');
                        $timesUsedByCustomer = $couponUsageByCustomerM->getTimesUsed();

                        if ($timesUsedByCustomer >= (int)$usagePerCustomer) {
                            return Mage::helper('zolagomodago')->__('The coupon code has already been used.');
                        }
                    }
                }
            }
        }

        //check if coupon does not meet conditions
        if ($couponM->getId()) {
            return Mage::helper('zolagomodago')->__('The coupon does meet conditions') . ': ' . $couponM->getDescription();

        }

        return $error;
    }
}