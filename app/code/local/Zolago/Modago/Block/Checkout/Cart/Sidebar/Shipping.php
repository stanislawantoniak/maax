<?php

class Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping
    extends Zolago_Modago_Block_Checkout_Onepage_Abstract
{
    /**
     * @return string
     */
    public static function getDeliveryTypeInpost()
    {
        $ghInpostCarrierCode = Mage::getModel("ghinpost/carrier")->getCarrierCode();
        return $ghInpostCarrierCode;
    }

    /** Is delivery method required additional locker
     * (ex. Inpost locker name, Pick-up point)
     * @param $deliveryType
     * @return bool
     */
    public function isDeliveryPointSelectRequired($deliveryType)
    {
        $isDeliveryPointSelectRequired = false;
        if (empty($deliveryType))
            return $isDeliveryPointSelectRequired;


        if (in_array($deliveryType, $this->getDeliveryMethodsPointSelectRequired()))
            $isDeliveryPointSelectRequired = true;

        return $isDeliveryPointSelectRequired;
    }


    /**
     * Delivery methods, that required additional info entrance
     * (ex. Inpost locker name, Pick-up point)
     *
     * @return array
     */
    public function getDeliveryMethodsPointSelectRequired()
    {
        $ghInpostCarrierCode = Mage::getModel("ghinpost/carrier")->getCarrierCode(); //Inpost locker
        $pickUpPointCode = Mage::helper("zospickuppoint")->getCode(); //Pick-up point

        return array($ghInpostCarrierCode, $pickUpPointCode);
    }





    public function getDeliveryDataAdditional($deliveryMethodCode, $deliveryPointIdentifier)
    {
        $additionalData = "";

        switch ($deliveryMethodCode) {
            case 'zolagopickuppoint':
                $pos = Mage::getModel("zolagopos/pos")->load($deliveryPointIdentifier);
                $additionalData = $this->getPickUpPointRender($pos);
                break;
            case 'ghinpost':
                $locker = $this->getInpostLocker();
                $additionalData = $this->getLockerRender($locker);
                break;
        }
        return $additionalData;
    }


    /**
     * @return array
     */
    public function getItemsShippingCost()
    {
        $data = array();
        $qRates = $this->getRates();

        $cost = array();
        if (!empty($qRates)) {
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
    public function getRates()
    {
        $q = Mage::getSingleton('checkout/session')->getQuote();
        $a = $q->getShippingAddress();

        $qRates = $a->getGroupedAllShippingRates();
        /**
         * Fix rate quto query
         */
        if (!$qRates) {
            $a->setCountryId(Mage::app()->getStore()->getConfig("general/country/default"));
            $a->setCollectShippingRates(true);
            $a->collectShippingRates();
            $qRates = $a->getGroupedAllShippingRates();
        }

        return $qRates;
    }



    /**
     * @param Zolago_Pos_Model_Pos $pos
     * @return string
     */
    public function getPickUpPointRender(Zolago_Pos_Model_Pos $pos)
    {
        $result = "";
        if ($pos->getId()) {
            $dataLines = array(
                $pos->getStreet(),
                $pos->getPostcode() . " " . $pos->getCity()
            );
            $result = implode("<br />", $dataLines);
        }

        return $result;
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
                $locker->getStreet() . " " . $locker->getBuildingNumber(),
                $locker->getPostcode() . " " . $locker->getTown(),
                "(" . $locker->getLocationDescription() . ")"
            );
            $result = implode("<br />", $lockerDataLines);
        }

        return $result;
    }

} 