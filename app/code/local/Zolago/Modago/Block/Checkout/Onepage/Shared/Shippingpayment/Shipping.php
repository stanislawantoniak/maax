<?php

class Zolago_Modago_Block_Checkout_Onepage_Shared_Shippingpayment_Shipping
    extends Zolago_Modago_Block_Checkout_Onepage_Abstract
{


    public function getItems()
    {

        $methods = array();
        $rates = array();

        $methodsByCode = array();

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
                $methodsByCode[$rate->getCode()] = array(
                    'code' => $rate->getCode(),
                    'carrier_title' => $rate->getData('carrier_title'),
                    'method_title' => $rate->getData('method_title')
                );
            }
            unset($cRates);
            unset($rate);
        }
        $methodToFind = array();

        foreach ($methods as $vendorId => $methodData) {
                $methodToFind[$methodData['code']][] = $vendorId;
        }

        //Find intersecting method for all vendors
        $allVendorsMethod = '';
        foreach ($methodToFind as $method => $vendorsInMethod) {
            $diff = array_diff($vendors, $vendorsInMethod);
            if (empty($diff)) {
                $allVendorsMethod = $method;
            }
        }

        return (object)array('rates' => $rates, 'allVendorsMethod' => $allVendorsMethod, 'vendors' => $vendors, 'methods' => $methodsByCode);

    }

    /**
     * Shipping cost by vendor
     * [[vendor_1] => cost_1, [vendor_2] => cost_2]
     * @return array
     */
    public function getItemsShippingCost()
    {
        $data = array();
        $qRates = $this->getRates();

        foreach ($qRates as $cCode => $cRates) {
            foreach ($cRates as $rate) {

                $vId = $rate->getUdropshipVendor();
                if (!$vId) {
                    continue;
                }
                $data[$vId] = $rate->getPrice();
            }
        }
        return $data;
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