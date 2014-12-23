<?php

/**
 * Licentia Fidelitas - Advanced Email and SMS Marketing Automation for E-Goi
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * @title      Advanced Email and SMS Marketing Automation
 * @category   Marketing
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) 2012 Licentia - http://licentia.pt
 * @license    Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 */
class Licentia_Fidelitas_Model_Coupons extends Mage_Core_Model_Abstract {

    protected function _construct() {

        $this->_init('fidelitas/coupons');
    }

    public function toOptionArray() {

        $model = Mage::getModel('salesrule/rule')->getCollection()
                ->addFieldToSelect('name')
                ->addFieldToSelect('rule_id');

        $return = array();

        foreach ($model as $rule) {
            $return[] = array('value' => $rule->getId(), 'label' => $rule->getName());
        }
        return $return;
    }

    public function toFormValues() {

        $values = $this->toOptionArray();

        $return = array();
        foreach ($values as $rule) {
            $return[$rule['value']] = $rule['label'];
        }

        return $return;
    }

    public function couponAfterOrder($event) {

        $order = $event->getEvent()->getOrder();

        if (!$order->getCouponCode()) {
            return true;
        }

        $coupon = $order->getCouponCode();

        $collection = Mage::getModel('fidelitas/coupons')
                ->getCollection()
                ->addFieldToFilter('coupon_code', $coupon);

        if ($collection->count() != 1) {
            return false;
        }

        $model = $collection->getFirstItem();

        return $model->setData('times_used', 1)
                ->setData('order_id', $order->getId())
                ->setData('used_at', now())
                ->save();
    }

    public function validateCoupon($coupon) {

        $collection = Mage::getModel('fidelitas/coupons')->getCollection()
                ->addFieldToFilter('coupon_code', $coupon);

        if ($collection->count() != 1) {
            return true;
        }

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
        } else {
            return false;
        }

        if ($collection->count() == 1 &&
                (int) $collection->getFirstItem()->getData('times_used') == 0 &&
                $customer->getEmail() == $collection->getFirstItem()->getSubscriberEmail()) {
            return true;
        }

        return false;
    }

    public function getCoupon($params) {

        if (version_compare(Mage::getVersion(), '1.7') == -1) {
            return false;
        }

        $subscriber = Mage::registry('fidelitas_current_subscriber');
        $campaign = Mage::registry('fidelitas_current_campaign');
        $customer = Mage::registry('current_customer');
        if (!$subscriber) {
            return '';
        }
        if (!$campaign) {
            return '';
        }

        $rule = Mage::getModel('salesrule/rule')->load($params['rule']);
        if (!$rule->getId())
            return;


        $coupon = Mage::getModel('fidelitas/coupons')->getCollection()
                ->addFieldToFilter('subscriber_email', $subscriber->getEmail())
                ->addFieldToFilter('rule_id', $rule->getId())
                ->addFieldToFilter('campaign_id', $campaign->getId());

        $deleted = false;

        if ($coupon->count() == 1) {
            $tmpCoupon = $coupon->getFirstItem()->getCouponCode();
            $tmpCollection = Mage::getResourceModel('salesrule/coupon_collection')
                    ->addFieldToFilter('code', $tmpCoupon);

            if ($tmpCollection->count() == 0) {
                $coupon->getFirstItem()->delete();
                $deleted = true;
            }
        }

        if ($coupon->count() == 0 || $deleted === true) {

            if ($rule->getUsesPerCoupon() != 1 || $rule->getUsesPerCustomer() != 1) {
                $rule->setUsesPerCoupon(1)->setUsesPerCustomer(1)->save();
            }

            $generator = Mage::getModel('salesrule/coupon_massgenerator');

            if (!isset($params['prefix'])) {
                $params['prefix'] = '';
            }
            if (!isset($params['suffix'])) {
                $params['suffix'] = '';
            }

            $data = array(
                'uses_per_customer' => 1,
                'uses_per_coupon' => 1,
                'qty' => 1,
                'length' => (int) $params['length'] == 0 ? 10 : $params['length'],
                'to_date' => $rule->getToDate(),
                'format' => $params['format'],
                'suffix' => $params['suffix'],
                'dash' => $params['dash'],
                'prefix' => $params['prefix'],
                'rule_id' => $rule->getId()
            );

            $generator->validateData($data);

            $generator->setData($data);
            $generator->generatePool();
            $collection = Mage::getResourceModel('salesrule/coupon_collection')
                    ->addRuleToFilter($rule)
                    ->addGeneratedCouponsFilter()
                    ->setOrder('coupon_id', 'DESC')
                    ->setPageSize(1);

            if ($generator->getGeneratedCount() == 1 && $collection->count() == 1) {
                $couponRule = $collection->getFirstItem();

                $data = array();
                $data['coupon_code'] = $couponRule->getCode();
                $data['rule_id'] = $rule->getId();
                $data['subscriber_email'] = $subscriber->getEmail();
                $data['force'] = $params['force'];
                $data['customer_id'] = $customer->getId();
                $data['campaign_id'] = $campaign->getId();
                $data['created_at'] = now();

                $coupon = Mage::getModel('fidelitas/coupons')->setData($data)->save();
                return $coupon->getCouponCode();
            } else {
                return '';
            }
        } elseif ($coupon->count() == 1) {
            return $coupon->getFirstItem()->getCouponCode();
        }

        return '';
    }

}
