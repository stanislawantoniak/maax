<?php

class Zolago_Modago_Block_Checkout_Onepage_Shared_Shippingpayment_Shipping
    extends Zolago_Modago_Block_Checkout_Onepage_Abstract
{


    public function getItems()
    {
        $q = Mage::getSingleton('checkout/session')->getQuote();
        $a = $q->getShippingAddress();
        $methods = array();

        $details = $a->getUdropshipShippingDetails();
        if ($details) {
            $details = Zend_Json::decode($details);
            $methods = isset($details['methods']) ? $details['methods'] : array();
        }

        $qRates = $this->getRates();
        $vendors = array();
        foreach ($qRates as $cCode => $cRates) {
            foreach ($cRates as $rate) {
                $vId = $rate->getUdropshipVendor();
                if (!$vId) {
                    continue;
                }

                $rates[$vId][$cCode][] = $rate;
                $vendors[$vId] = $vId;
            }
            unset($cRates);
            unset($rate);
        }
        $methodToFind = array();
        foreach ($methods as $vendorId => $methodData) {
                $methodToFind[$methodData['code']][] = $vendorId;
        }

        //Find good method
        $allVendorsMethod = '';
        foreach ($methodToFind as $method => $vendorsInMethod) {
            $diff = array_diff($vendors, $vendorsInMethod);
            if (empty($diff)) {
                $allVendorsMethod = $method;
            }
        }

        return (object)array('rates' => $rates, 'allVendorsMethod' => $allVendorsMethod, 'vendors' => $vendors);

    }


    /**
     * @return mixed
     */
    public function getRates(){
        $q = Mage::getSingleton('checkout/session')->getQuote();
        $a = $q->getShippingAddress();

        $qRates = $a->getGroupedAllShippingRates();
        /**
         * Fix rate quto query
         */
        if(!$qRates){
            $a->setCountryId(Mage::app()->getStore()->getConfig("general/country/default"));
            $a->setCollectShippingRates(true);
            $a->collectShippingRates();
            $qRates = $a->getGroupedAllShippingRates();
        }

        return $qRates;
    }

} 