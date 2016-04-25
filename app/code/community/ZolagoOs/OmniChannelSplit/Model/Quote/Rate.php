<?php
/**
  
 */

class ZolagoOs_OmniChannelSplit_Model_Quote_Rate extends Mage_Sales_Model_Quote_Address_Rate
{
    public function importShippingRate(Mage_Shipping_Model_Rate_Result_Abstract $rate)
    {
        parent::importShippingRate($rate);
        $this->setUdropshipVendor($rate->getUdropshipVendor());
        return $this;
    }
}