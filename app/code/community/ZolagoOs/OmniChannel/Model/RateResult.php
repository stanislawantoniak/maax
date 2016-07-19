<?php
/**
  
 */

class ZolagoOs_OmniChannel_Model_RateResult extends Mage_Shipping_Model_Rate_Result
{
    public function sortRatesByPriority ()
    {
        if (!is_array($this->_rates) || !count($this->_rates)) {
            return $this;
        }
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */
        foreach ($this->_rates as $i => $rate) {
            $cmpPrice = $rate->hasBeforeExtPrice() ? $rate->getBeforeExtPrice() : $rate->getPrice();
            $tmp[$i] = 100*$rate->getPriority()+$cmpPrice+(int)$rate->getIsExtraCharge();
        }

        natsort($tmp);

        foreach ($tmp as $i => $price) {
            $result[] = $this->_rates[$i];
        }

        $this->reset();
        $this->_rates = $result;
        return $this;
    }
    public function sortRatesByPrice()
    {
        if (Mage::getStoreConfigFlag('zolagoos/customer/allow_shipping_extra_charge')) {
            $this->sortRatesByPriority();
        } else {
            parent::sortRatesByPrice();
        }
        return $this;
    }
}