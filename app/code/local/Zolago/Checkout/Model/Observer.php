<?php

class Zolago_Checkout_Model_Observer
{


    /**
     * @return Varien_Object
     */
    public function getOmniChannelMethodInfoByMethod($udropshipMethod)
    {
        // udropship_method (example udtiership_1)
        $storeId = Mage::app()->getStore()->getId();

        $collection = Mage::getModel("udropship/shipping")->getCollection();
        $collection->getSelect()
            ->join(
                array('udropship_shipping_method' => $collection->getTable('udropship/shipping_method')),
                "main_table.shipping_id = udropship_shipping_method.shipping_id",
                array(
                    'udropship_method' => new Zend_Db_Expr('CONCAT_WS(\'_\',    udropship_shipping_method.carrier_code ,udropship_shipping_method.method_code)'),
                    "udropship_method_title" => "IF(udropship_shipping_title_store.title IS NOT NULL, udropship_shipping_title_store.title, udropship_shipping_title_default.title)"
                )
            );
        $collection->getSelect()->join(
            array('udtiership_delivery_type' => $collection->getTable('udtiership/delivery_type')),
            "udropship_shipping_method.method_code = udtiership_delivery_type.delivery_type_id",
            array("delivery_code")
        );

        $collection->getSelect()->joinLeft(
            array('udropship_shipping_title_default' => $collection->getTable('udropship/shipping_title')),
            "main_table.shipping_id = udropship_shipping_title_default.shipping_id AND udropship_shipping_title_default.store_id=0",
            array()
        );
        $collection->getSelect()->joinLeft(
            array('udropship_shipping_title_store' => $collection->getTable('udropship/shipping_title')),
            "main_table.shipping_id = udropship_shipping_title_store.shipping_id AND udropship_shipping_title_store.store_id={$storeId}",
            array()
        );

        $collection->getSelect()->having("udropship_method=?", $udropshipMethod);

        return $collection->getFirstItem();
    }

    public function paymentMethodIsActive($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        Mage::log($quote->getData(), null, "checkout.log");
        /* @var $quote Mage_Sales_Model_Quote */

        $address = $quote->getShippingAddress();

        $details = $address->getUdropshipShippingDetails();
        $details = $details ? Zend_Json::decode($details) : array();
        Mage::log($details["methods"], null, "checkoutShipping.log");
        $methods = array_values($details["methods"]);

        $udropshipMethod = array_shift($methods)["code"];
        Mage::log($udropshipMethod, null, "checkoutShipping.log");


        $info = $this->getOmniChannelMethodInfoByMethod($udropshipMethod);
        Mage::log($info->getDeliveryCode(), null, "checkoutInfo.log");


    }
}
