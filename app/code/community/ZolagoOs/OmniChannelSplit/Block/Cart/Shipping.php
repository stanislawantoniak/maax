<?php
/**
  
 */

class ZolagoOs_OmniChannelSplit_Block_Cart_Shipping extends Mage_Checkout_Block_Cart_Shipping
{
    public function getEstimateRates()
    {
        if (!Mage::helper('udsplit')->isActive()) {
            return parent::getEstimateRates();
        }

        if (empty($this->_rates)) {
            $groups = $this->getAddress()->getGroupedAllShippingRates();
            foreach ($groups as $cCode=>$rates) {
                foreach ($rates as $i=>$rate) {
                    if ($rate->getUdropshipVendor() || $rate->getCarrier()=='udsplit' && $rate->getMethod()=='total') {
                        unset($groups[$cCode][$i]);
                    }
                    if (empty($groups[$cCode])) {
                        unset($groups[$cCode]);
                    }
                }
            }
            $this->_rates = $groups;
        }
        return $this->_rates;
    }
}