<?php

/**
 * Class Zolago_Sales_Model_Service_Quote
 */
class Zolago_Sales_Model_Service_Quote extends Mage_Sales_Model_Service_Quote {
    /**
     * Validate quote data before converting to order
     *
     * @return Mage_Sales_Model_Service_Quote
     */
    protected function _validate()
    {
        if (!$this->getQuote()->isVirtual()) {
            $address = $this->getQuote()->getShippingAddress();
            $addressValidation = $address->validate();
            if ($addressValidation !== true) {
                Mage::throwException(
                    Mage::helper('sales')->__('Please check shipping address information. %s', implode(' ', $addressValidation))
                );
            }
            $method= $address->getShippingMethod();
            $rate  = $address->getShippingRateByCode($method);
            if (!$this->getQuote()->isVirtual() && (!$method || !$rate)) {
                Mage::throwException(Mage::helper('sales')->__('Please specify a shipping method.'));
            }
        }

        $addressValidation = $this->getQuote()->getBillingAddress()->validate();

        /** @var Zolago_Checkout_Helper_Data $helper */
        $helper = Mage::helper("zolagocheckout");
        $deliveryPointAddress = $helper->getDeliveryPointShippingAddress();

        if ($addressValidation !== true && empty($deliveryPointAddress)) {
            Mage::throwException(
                Mage::helper('sales')->__('Please check billing address information. %s', implode(' ', $addressValidation))
            );
        }

        if (!($this->getQuote()->getPayment()->getMethod())) {
            Mage::throwException(Mage::helper('sales')->__('Please select a valid payment method.'));
        }

        return $this;
    }
}