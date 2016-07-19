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


    /**
     * @param $deliveryMethod
     * @param $deliveryPointIdentifier
     * @return array
     */
    public function getDeliveryPointData($deliveryMethod, $deliveryPointIdentifier)
    {
        $data = array();

        $deliveryMethodData = $this->getMethodCodeByDeliveryType($deliveryMethod);
        $deliveryMethodCode = $deliveryMethodData->getDeliveryCode();

        switch ($deliveryMethodCode) {
            case 'zolagopickuppoint':
                $pos = Mage::getModel("zolagopos/pos")->load($deliveryPointIdentifier);
                $data = array(
                    "id" => $pos->getId(),
                    "city" => (string)ucwords(strtolower($pos->getCity())),
                    "value" => $pos->getId()
                );
                break;
            case 'ghinpost':
                /* @var $locker GH_Inpost_Model_Locker */
                $locker = $this->getInpostLocker();

                $data = array(
                    "id" => $locker->getId(),
                    "city" => (string)ucwords(strtolower($locker->getTown())),
                    "value" => $locker->getName()
                );
                break;
        }
        return $data;
    }


    public function getDeliveryDataAdditional($deliveryMethodCode, $deliveryPointIdentifier, $daysInTransit)
    {
        $additionalData = "";

        switch ($deliveryMethodCode) {
            case 'zolagopickuppoint':
                $pos = Mage::getModel("zolagopos/pos")->load($deliveryPointIdentifier);
                $additionalData = '<div data-item="additional">' . $this->getPickUpPointRender($pos) . '</div><div data-item="description">' . $daysInTransit . '</div>';
                break;
            case 'ghinpost':
                $locker = $this->getInpostLocker();

                $additionalData = '<div data-item="additional">' . $this->getLockerRender($locker) . '</div><div data-item="description">' . $daysInTransit . '</div>';
                break;
        }
        return $additionalData;
    }


    /**
     * @param $deliveryMethod (something like udtiership_4)
     * @param bool $includeTitle
     * @return Varien_Object
     */
    public function getMethodCodeByDeliveryType($deliveryMethod, $includeTitle = false){
        $storeId = Mage::app()->getStore()->getStoreId();

        $collection = Mage::getModel("udropship/shipping")->getCollection();
        $collection->getSelect()
            ->join(
                array('udropship_shipping_method' => $collection->getTable('udropship/shipping_method')),
                "main_table.shipping_id = udropship_shipping_method.shipping_id",
                array(
                    'udropship_method' => new Zend_Db_Expr('CONCAT_WS(\'_\',    udropship_shipping_method.carrier_code ,udropship_shipping_method.method_code)'),
                )
            );
        $collection->getSelect()->join(
            array('udtiership_delivery_type' => $collection->getTable('udtiership/delivery_type')),
            "udropship_shipping_method.method_code = udtiership_delivery_type.delivery_type_id",
            array("delivery_code")
        );

        if($includeTitle){
            $collection->getSelect()->joinLeft(
                array('udropship_shipping_title_default' => $collection->getTable('udropship/shipping_title')),
                "main_table.shipping_id = udropship_shipping_title_default.shipping_id AND udropship_shipping_title_default.store_id=0",
                array(
                    "udropship_method_title" => "IF(udropship_shipping_title_store.title IS NOT NULL, udropship_shipping_title_store.title, udropship_shipping_title_default.title)"
                )
            );
            $collection->getSelect()->joinLeft(
                array('udropship_shipping_title_store' => $collection->getTable('udropship/shipping_title')),
                "main_table.shipping_id = udropship_shipping_title_store.shipping_id AND udropship_shipping_title_store.store_id={$storeId}",
                array()
            );
        }

        $collection->getSelect()->having("udropship_method=?", $deliveryMethod);

        return $collection->getFirstItem();
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