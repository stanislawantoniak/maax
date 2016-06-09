<?php

class Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping
    extends Zolago_Modago_Block_Checkout_Onepage_Abstract
{
    /**
     * @return string
     */
    public static function getDeliveryTypeInpost(){
        $ghInpostCarrierCode = Mage::getModel("ghinpost/carrier")->getCarrierCode();
        return $ghInpostCarrierCode;
    }



    /**
     * @return array
     */
    public function getItemsShippingCost()
    {
        $data = array();
        $qRates = $this->getRates();

        $cost = array();
        if(!empty($qRates)){
            foreach ($qRates as $cRates) {
                foreach ($cRates as $rate) {
                    $vId = $rate->getUdropshipVendor();

                    if (!$vId) {
                        continue;
                    }
                    $data[$vId][$rate->getCode()] = $rate->getPrice();
                }
                unset($rate);
            }

            unset($cRates);

            if (!empty($data)) {
                foreach ($data as $vId => $dataItem) {
                    $cost[$vId] = array_sum($dataItem);
                }
            }
        }
        return $cost;
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


    /**
     * @param GH_Inpost_Model_Locker $locker
     * @return string
     */
    public function getLockerRender(GH_Inpost_Model_Locker $locker)
    {
        $result = "";
        if ($locker->getId()) {
            $lockerDataLines = array(
                $locker->getStreet() . " ". $locker->getBuildingNumber(),
                $locker->getPostcode() . " " . $locker->getTown(),
                "(" . $locker->getLocationDescription() . ")"
            );
            $result = implode("<br />", $lockerDataLines);
        }

        return $result;
    }

} 