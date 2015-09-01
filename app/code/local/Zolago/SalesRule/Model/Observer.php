<?php
/**
 * The flow of discount info:
 *
 * 1. After process rule (salesrule_validator_process event) register discount_info
 * as part of quote item object.
 *
 * 2. During placing order via <fieldsets> config copy the discount info array
 * to order item
 *
 * 3. After order item save make save the realtion
 *
 * During create the PO item need to update te relation with po_item_id
 * During update po item manualy need to remove an relation entry
 */
class Zolago_SalesRule_Model_Observer {

    /**
     * Add rule_id=>price to quote item
     * @param Varien_Event_Observer $observer
     */
    public function salesruleValidatorProcess(Varien_Event_Observer $observer) {

        $event = $observer->getEvent();
        $item = $event->getItem();
        /* @var $item Mage_Sales_Model_Quote_Item */
        $result = $event->getResult();
        /* @var $result Varien_Object */

        $rule = $event->getRule();
        /* @var $rule Mage_SalesRule_Model_Rule */
        $ruleId = $rule->getId();

        $discount = $result->getDiscountAmount();

        $discountInfo = $item->getDiscountInfo();

        if(!is_array($discountInfo)) {
            $discountInfo = array();
        }

        if($discount) {
            $discountInfo[$ruleId] = array(
                                         "rule_id"			=> $ruleId,
                                         "discount_amount"	=> $discount,
                                         "name"				=> $rule->getName(),
                                         "payer"				=> $rule->getRulePayer(),
                                         "simple_action"		=> $rule->getSimpleAction()
                                     );
        } else {
            unset($discountInfo[$ruleId]);
        }
        // Set property of abstract object (property not stored)
        $item->setDiscountInfo($discountInfo);
    }

    /**
     * Da save relation based on quote item -> convert -> order item flow
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderItemSaveAfter(Varien_Event_Observer $observer) {
        $item = $observer->getEvent()->getItem();
        /* @var $item Mage_Sales_Model_Order_Item */
        if($item->getDiscountInfo()) {
            Mage::getResourceSingleton('zolagosalesrule/relation')->saveForOrderItem($item);
            // Unset discount info to prevent next steps saving
            $item->setDiscountInfo(null);
        }
    }

    /**
     * Register beofre save action
     * @param Varien_Event_Observer $observer
     */
    public function udpoPoItemSaveBefore(Varien_Event_Observer $observer) {
        $item = $observer->getEvent()->getPoItem();
        /* @var $item Unirgy_DropshipPo_Model_Po_Item */
        if($item->isObjectNew()) {
            if (Mage::registry('vendor_add_item_to_po_before')) {
                $item->setDoResetDiscountInfo(true);
            } else {
                $item->setDoUpdateDiscountInfo(true);
            }
        }
        elseif($this->_hasDiscountChanged($item)) {
            $item->setDoResetDiscountInfo(true);
        }
    }

    /**
     * Discount can be changed by on of the paramters:
     *	- discount_amount
     *  - price_incl_tax
     * @param Unirgy_DropshipPo_Model_Po_Item $item
     * @return bool
     */
    protected function _hasDiscountChanged(Unirgy_DropshipPo_Model_Po_Item $item) {
        return
            (round($item->getData("discount_amount"), 2) !== round($item->getOrigData("discount_amount"), 2)) ||
            (round($item->getData("price_incl_tax"), 2) !== round($item->getOrigData("price_incl_tax"), 2));
    }

    /**
     * Da save relation based on quote item -> convert -> order item -> po item
     * @param Varien_Event_Observer $observer
     */
    public function udpoPoItemSaveAfter(Varien_Event_Observer $observer) {
        $item = $observer->getEvent()->getPoItem();
        /* @var $item Unirgy_DropshipPo_Model_Po_Item */
        if($item->getDoUpdateDiscountInfo()) {
            Mage::getResourceSingleton('zolagosalesrule/relation')->updateForPoItem($item);
            $item->setDoUpdateDiscountInfo(null);
        }

        if($item->getDoResetDiscountInfo()) {
            Mage::getResourceSingleton('zolagosalesrule/relation')->resetDiscountInfo($item);
            $item->getDoResetDiscountInfo(null);
        }
    }

    /**
     * Add a payer field to form
     * @param Varien_Event_Observer $observer
     */
    public function adminhtmlPromoQuoteEditTabMainPrepareForm(Varien_Event_Observer $observer) {
        $form = $observer->getEvent()->getForm();
        /* @var $form Varien_Data_Form */
        $fieldset = $form->getElement("base_fieldset");
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */
        $model = Mage::registry('current_promo_quote_rule');
        /* @var $model Mage_SalesRule_Model_Rule */
        $fieldset->addField("rule_payer", "select", array(
                                "label"		=> Mage::helper("zolagosalesrule")->__("Payer"),
                                "values"	=> Mage::getSingleton('zolagosalesrule/rule_payer')->toOptionArray(),
                                "name"		=> "rule_payer",
                                "value"     => $model->getRulePayer()
                            ), "name");
        $fieldset->addField("promotion_type", "select", array(
                                "label"		=> Mage::helper("zolagosalesrule")->__("Promotion type"),
                                "values"	=> Mage::getSingleton('zolagosalesrule/promotion_type')->toOptionArray(),
                                "name"		=> "promotion_type",
                                "value"     => $model->getPromotionType()
                            ));
        $fieldset->addField("campaign_id", "select", array(
                                "label"		=> Mage::helper("zolagosalesrule")->__("Campaign"),
                                "values"	=> Mage::getSingleton('zolagocampaign/source_campaign')->toOptionArray(true, $model->getId()),
                                "name"		=> "campaign_id",
                                "value"     => $model->getCampaignId()
                            ));
        $param = array(
                     "label" => Mage::helper('zolagosalesrule')->__('Promotion image file'),
                     "required" => false,
                     "name"		=> "promo_image",
                 );
        if ($model->getPromoImage()) {
            $param["value"] = Mage::helper('zolagosalesrule')->getPromotionImageUrl().DS.$model->getPromoImage();
        }

        $fieldset->addField("promo_image", "image", $param);
    }
   /**
     * Send new coupons to subscriber
     */
    public static function sendSubscriberCouponMail(Varien_Event_Observer $observer)
    {
	    /** @var Zolago_Salesrule_Helper_Data $helper */
	    $helper = Mage::helper('zolagosalesrule');
        $model = $observer->getEvent()->getSubscriber();
        $newStatus = $model->getData('subscriber_status');
        if ($newStatus != Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
            return;
        }
        $customerId = $model->getCustomerId();
        $subscribersCustomersId = array ($customerId);
        $subscribers[$model->getId()] = $model->getSubscriberEmail();
        $subscribersCustomersId[$model->getSubscriberEmail()] = $model->getCustomerId();
        $subscribersCustomersSubscribers[$model->getCustomerId()] = $model->getSubscriberEmail();



        //1. Coupons
	    $result = $helper->getSalesRulesForSubscribers();

        //Group coupons by rule
        if (empty($result)) {
            return;
        }

        //Find if customer already got coupon in rule
        $resultRules = $helper->getSalesRulesCouponsByCustomers(array_values($subscribersCustomersId));

        $rulesForCustomer = array();
        foreach($resultRules as $resultRulesItem){
            $rulesForCustomer[$resultRulesItem["rule_id"]][] = $subscribersCustomersSubscribers[$resultRulesItem["customer_id"]];
        }

        $coupons = array();
        foreach ($result as $couponData) {
            $coupons[$couponData['rule_id']][$couponData['coupon_id']] = $couponData['coupon_id'];
        }


        //3. Assign coupons to customers
        $data = array(
            "subscribers" => $subscribers,
            "coupons" => $coupons,
            "data_to_send" => array()
        );

        $res = $helper->assignCouponsToSubscribers($data, $rulesForCustomer);


        //4. Send mails
        $dataAssign = $res["data_to_send"];
        if (!empty($dataAssign)) {
            foreach ($dataAssign as $email => $sendData) {
                $customerId = isset($subscribersCustomersId[$email]) ? $subscribersCustomersId[$email] : false;
                if($customerId){
                    if (!$helper->sendPromotionEmail($customerId, array_values($sendData))) {
                        //if mail sending failed
                        unset($dataAssign[$email]);
                    }
                }
            }
            unset($customerId);
        }

        //5. Set customer_id to salesrule_coupon table
        if (!empty($dataAssign)) {
            $insertData = array();
            foreach ($dataAssign as $email => $sendData) {
                foreach($sendData as $couponId){
                    if(isset($subscribersCustomersId[$email])){
                        $insertData[] = "({$couponId},".$subscribersCustomersId[$email].")";
                    }
                }
            }

            if(!empty($insertData)){
                $insertData = implode(",", $insertData);
                /* @var $salesCouponModel Zolago_SalesRule_Model_Resource_Coupon */
                $salesCouponModel = Mage::getResourceModel("salesrule/coupon");
                $salesCouponModel->bindCustomerToCoupon($insertData);
            }

        }
    }
}