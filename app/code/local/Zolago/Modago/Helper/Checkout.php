<?php

class Zolago_Modago_Helper_Checkout extends Mage_Core_Helper_Abstract
{

    /**
     * @return array
     */
    public function getShippingCostSummary()
    {
        $q = Mage::getSingleton('checkout/cart')->getQuote();
        $q->getTotals();


        /*shipping_cost*/
        $a = $q->getShippingAddress();

        $qRates = $a->getGroupedAllShippingRates();
        /**
         * Fix rate quote query
         */
        if (!$qRates) {
            $a->setCountryId(Mage::app()->getStore()->getConfig("general/country/default"));
            $a->setCollectShippingRates(true);
            $a->collectShippingRates();
            $qRates = $a->getGroupedAllShippingRates();
        }
        $cost = array();
        foreach ($qRates as $cRates) {
            foreach ($cRates as $rate) {

                $vId = $rate->getUdropshipVendor();
                if (!$vId) {
                    continue;
                }
                $data[$vId][] = $rate->getPrice();
            }
            unset($rate);
        }
        unset($cRates);
        if (!empty($data)) {
            foreach ($data as $vId => $dataItem) {
                $cost[$vId] = array_sum($dataItem);
            }
        }
        return $cost;
    }

}
