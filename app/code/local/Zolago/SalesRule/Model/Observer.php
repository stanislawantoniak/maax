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
    public static function sendSubscriberCouponMail()
    {
        //1. Coupons
        $currentTimestamp = Mage::getModel('core/date')->timestamp(time());

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');

        $query = $readConnection
            ->select()
            ->from(
                array('salesrule_rule' => $resource->getTableName("salesrule/rule")),
                array("rule_id", "name", "from_date", "to_date", "coupon_type")
            )
            ->joinLeft(array('salesrule_coupon' => $resource->getTableName("salesrule/coupon")),
                'salesrule_rule.rule_id = salesrule_coupon.rule_id',
                array("coupon_id","code")
            )
            ->where('salesrule_rule.is_active = ?', 1)
            ->where('customer_id IS NULL')
            ->where('salesrule_rule.to_date >= ?', date("Y-m-d H:i:s", $currentTimestamp))
            ->where('salesrule_rule.promotion_type = ?', Zolago_SalesRule_Model_Promotion_Type::PROMOTION_SUBSCRIBERS)//->group("salesrule_rule.rule_id")

        ;
        $result = $readConnection->fetchAll($query);

        //Group coupons by rule
        if (empty($result)) {
            exit;
        }
        //2. Subscribers
        $collection = Mage::getModel('newsletter/subscriber')
            ->getCollection()
            ->addFieldToFilter("subscriber_status", Zolago_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED)
            ;
        $collection->getSelect()
            ->joinLeft(array("customer" => "customer_entity"),
                "main_table.subscriber_email = customer.email",
                array("customer_id" => "customer.entity_id")
            )
            ->where("main_table.store_id=customer.store_id")
        ;
        $collection->setPageSize(10000);

        if ($collection->getSize() == 0) {
            exit;
        }
        $subscribersCollection = $collection->getItems();
        $subscribers = array();
        $subscribersCustomersId = array();
        foreach ($subscribersCollection as $subId => $subscriber) {
            $subscribers[$subId] = $subscriber->getSubscriberEmail();
            $subscribersCustomersId[$subscriber->getSubscriberEmail()] = $subscriber->getCustomerId();
        }

        $coupons = array();
        foreach ($result as $couponData) {
            $coupons[$couponData['rule_id']][$couponData['coupon_id']] = $couponData['coupon_id'];
        }

        //3. Assign coupons to customers
        $dataToSend = array();

        $data = array(
            "subscribers" => $subscribers,
            "coupons" => $coupons,
            "data_to_send" => $dataToSend
        );

        /* @var $helper Zolago_SalesRule_Helper_Data */
        $helper = Mage::helper("zolagosalesrule");
        $res = $helper->assignCouponsToSubscribers($data);
        //Mage::log($res["data_to_send"], null, "coupon5.log");


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