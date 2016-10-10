<?php
/**
 * Abstract for all steps
 */
abstract class Zolago_Modago_Block_Checkout_Onepage_Abstract extends Mage_Checkout_Block_Onepage_Abstract {



    /**
     * Delivery point info constructor for checkout
     * @return stdClass
     */
    public function getDeliveryPointCheckout() {

        /** @var Zolago_Checkout_Helper_Data $helper */
        $helper = Mage::helper("zolagocheckout");

        $deliveryMethodData = $helper->getMethodCodeByDeliveryType();
        $deliveryMethodCode = $deliveryMethodData->getDeliveryCode();


        $deliveryPoint = new stdClass();
        $deliveryPoint->id = NULL;
        $deliveryPoint->checkout = new stdClass();
        switch ($deliveryMethodCode) {
            case ZolagoOs_PickupPoint_Helper_Data::CODE:
                /* @var $pos  Zolago_Pos_Model_Pos */
                $pos = $helper->getPickUpPoint();

                $deliveryPoint->id = $pos->getId();
                $deliveryPoint->name = $pos->getName();
                $deliveryPoint->delivery_point_name = $pos->getId(); //this value will be saved to PO(delivery_point_name)
                $deliveryPoint->city = $pos->getCity();
                $deliveryPoint->street = $pos->getStreet();
                $deliveryPoint->buildingNumber = "";
                $deliveryPoint->postcode = $pos->getPostcode();
                $deliveryPoint->locationDescription = "";

                $deliveryPoint->checkout->title = $helper->__("Pick-Up Point");
                $deliveryPoint->checkout->logo = '<figure class="truck"><i class="fa fa-map-marker fa-3x"></i></figure>';
                $deliveryPoint->checkout->additionalInfo1 = "";
                $deliveryPoint->checkout->additionalInfo2 = "";
                break;
            case GH_Inpost_Model_Carrier::CODE:
                /* @var $locker GH_Inpost_Model_Locker */
                $locker = $helper->getInpostLocker();

                $deliveryPoint->id = $locker->getId();
                $deliveryPoint->name = $locker->getName();
                $deliveryPoint->delivery_point_name = $locker->getName(); //this value will be saved to PO(delivery_point_name)
                $deliveryPoint->city = $locker->getTown();
                $deliveryPoint->street = $locker->getStreet();
                $deliveryPoint->postcode = $locker->getPostcode();
                $deliveryPoint->buildingNumber = $locker->getBuildingNumber();
                $deliveryPoint->locationDescription = $locker->getLocationDescription();

                $deliveryPoint->checkout->title = $helper->__("Locker InPost");
                $deliveryPoint->checkout->logo = '<figure class="inpost-img"><div><img src="'.$this->getSkinUrl('images/inpost/checkout-logo.png').'"></div></figure><br/>';
                $deliveryPoint->checkout->additionalInfo1 = $helper->__("The phone number is required to receive package from locker.") . "<br/>";
                $deliveryPoint->checkout->additionalInfo2 = $helper->__("We do not use it in any other way without your permission!");
                break;
            case Orba_Shipping_Model_Packstation_Pwr::CODE:
                /* @var $locker ZolagoOs_Pwr_Model_Point */
                $point = $helper->getPwrPoint();

                $deliveryPoint->id = $point->getId();
                $deliveryPoint->name = $point->getName();
                $deliveryPoint->delivery_point_name = $point->getName();
                $deliveryPoint->city = $point->getTown();
                $deliveryPoint->street = $point->getStreet();
                $deliveryPoint->postcode = $point->getPostcode();
                $deliveryPoint->buildingNumber = $point->getBuildingNumber();
                $deliveryPoint->locationDescription = $point->getLocationDescription();

                $deliveryPoint->checkout->title = $helper->__("Locker PwR");
                $deliveryPoint->checkout->logo = '<figure class="pwr-img"><div><img src="'.$this->getSkinUrl('images/pwr/checkout-logo.png').'"></div></figure><br/>';
                $deliveryPoint->checkout->additionalInfo1 = $helper->__("The phone number is required to receive package from locker.") . "<br/>";
                $deliveryPoint->checkout->additionalInfo2 = $helper->__("We do not use it in any other way without your permission!");
                break;
        }

        return $deliveryPoint;
    }
    /**
     * @param $deliveryMethod
     * @param $deliveryPointIdentifier
     * @return array
     */
    public function getDeliveryPointData($deliveryPointIdentifier)
    {
        $data = array();

        /** @var Zolago_Checkout_Helper_Data $helper */
        $helper = Mage::helper("zolagocheckout");

        $deliveryMethodData = $helper->getMethodCodeByDeliveryType();
        $deliveryMethodCode = $deliveryMethodData->getDeliveryCode();

        switch ($deliveryMethodCode) {
            case ZolagoOs_PickupPoint_Helper_Data::CODE:
                $pos = Mage::getModel("zolagopos/pos")->load($deliveryPointIdentifier);
                $data = array(
                    "id" => $pos->getId(),
                    "city" => (string)ucwords(strtolower($pos->getCity())),
                    "value" => $pos->getId(),
                    "delivery_method_code" => $deliveryMethodCode
                );
                break;
            case GH_Inpost_Model_Carrier::CODE:
                /* @var $locker GH_Inpost_Model_Locker */
                $locker = $this->getInpostLocker();

                $data = array(
                    "id" => $locker->getId(),
                    "city" => (string)ucwords(strtolower($locker->getTown())),
                    "value" => $locker->getName(),
                    "delivery_method_code" => $deliveryMethodCode
                );
                break;
            case Orba_Shipping_Model_Packstation_Pwr::CODE:
                /* @var $locker ZolagoOs_Pwr_Model_Point */
                $point = $this->getPwrPoint();

                $data = array(
                    "id" => $point->getId(),
                    "city" => (string)ucwords(strtolower($point->getTown())),
                    "value" => $point->getName(),
                    "delivery_method_code" => $deliveryMethodCode
                );
                break;
        }
        return $data;
    }

    /**
     * @return GH_Inpost_Model_Locker
     */
    public function getInpostLocker() {
        /** @var Zolago_Checkout_Helper_Data $helper */
        $helper = Mage::helper("zolagocheckout");
        $locker = $helper->getInpostLocker();
        return $locker;
    }

    /**
     * clear delivery settings from session if delivery code is not allowed
     */
    public function clearDelivery($code) {
        $checkoutSession = Mage::getSingleton('checkout/session');
        if (!is_array($checkoutSession->getData("shipping_method"))) {
            return;
        }
        foreach ($checkoutSession->getData("shipping_method") as $method) {
            if ($code == $method) {
                $checkoutSession->setData('shipping_method',null);
                $checkoutSession->setData('delivery_point_name',null);
                $address = $this->getQuote()->getShippingAddress();
                $address->setShippingMethod(null);
                $address->setUdropshipShippingDetails(null);
                $address->save();
//                Mage::getSingleton('core/session')->addError('Your delivery method has been disabled');

            }
        }

    }

    /**
     * @return ZolagoOs_Pwr_Model_Point
     */
    public function getPwrPoint() {
        /** @var Zolago_Checkout_Helper_Data $helper */
        $helper = Mage::helper("zolagocheckout");
        $point = $helper->getPwrPoint();
        return $point;
    }


    /**
     * @return string
     */
    public function getLastTelephoneForLocker() {
        $shippingAddress = $this->getQuote()->getShippingAddress();
        $tel = $shippingAddress->getTelephone();
        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $checkoutSession = Mage::getSingleton('checkout/session');
        if ($checkoutSession->getLastTelephoneForLocker()) {
            $tel = $checkoutSession->getLastTelephoneForLocker();
        }
        return $tel;
    }


    /**
     * @return object
     */
    public function getUdropShippingMethods()
    {
        $model = Mage::getModel('udropship/shipping');
        $shipping = Mage::getModel('udropship/shipping')->getCollection();
        $shipping->getSelect()->join(
            array('udropship_shipping_method' => "udropship_shipping_method"),
            "main_table.shipping_id = udropship_shipping_method.shipping_id",
            array(
                'method_code' => 'udropship_shipping_method.method_code',
            )
        );
        $shipping->getSelect()->join(
            array('website_table' => $model->getResource()->getTable('udropship/shipping_website')),
            'main_table.shipping_id = website_table.shipping_id',
            array("website_table.website_id")
        )->where("website_table.website_id IN(?)", array(0, Mage::app()->getWebsite()->getId()));

        return $shipping;
    }
    public function calculateSumVolume() {
        $quote = $this->getQuote();
        $items = $quote->getAllVisibleItems();
        $sum = 0;
        foreach ($items as $item) {
            $value = Mage::getModel('catalog/product')->load($item->getProduct()->getId())->getDeliveryVolume();
            if ($value === null) {
                $value = Mage::getStoreConfig('shipping/option/default_delivery_volume');
            }
            $sum += $value*($item->getQty());
        }
        return $sum;
    }
    public function getRateItems()
    {
        $sumVolume = $this->calculateSumVolume();
        $rates = array();

        $methodsByCode = array();

        $qRates = $this->getRates();
        $allMethodsByCode = array();
        $vendors = array();

        $daysInTransitData = array();
        $shipping = $this->getUdropShippingMethods();
        $shippingMethods = array();

        foreach($shipping as $shippingItem) {
            $daysInTransitData[$shippingItem->getMethodCode()] = $shippingItem->getDaysInTransit();
            $shippingMethods[$shippingItem->getMethodCode()] = $shippingItem->getData();

        }
        foreach ($qRates as $cCode => $cRates) {

            foreach ($cRates as $rate) {
                /* @var $rate Unirgy_DropshipSplit_Model_Quote_Rate */
                $vId = $rate->getUdropshipVendor();

                if (!$vId) {
                    continue;
                }
                $rates[$vId][$cCode][] = $rate;
                $vendors[$vId] = $vId;

                $deliveryType = "";
                $deliveryTypeModel = Mage::getModel("udtiership/deliveryType")->load($rate->getMethod());
                if ($deliveryTypeModel->getId()) {
                    $deliveryType = $deliveryTypeModel->getDeliveryCode();
                }


                if(isset($shippingMethods[$rate->getMethod()])) {
                    $methodsByCode[$rate->getCode()] = array(
                        'vendor_id' => $vId,
                        'code' => $rate->getCode(),
                        'carrier_title' => $rate->getData('carrier_title'),
                        'method_title' => $rate->getData('method_title'),
                        'days_in_transit' => (isset($daysInTransitData[$rate->getMethod()]) ? $daysInTransitData[$rate->getMethod()] : ""),
                        "delivery_type" => $deliveryType
                    );

                    $allMethodsByCode[$rate->getCode()][] = array(
                        'vendor_id' => $vId,
                        'code' => $rate->getCode(),
                        'carrier_title' => $rate->getData('carrier_title'),
                        'method_title' => $rate->getData('method_title'),
                        'cost' => $rate->getPrice(),
                        'days_in_transit' => (isset($daysInTransitData[$rate->getMethod()]) ? $daysInTransitData[$rate->getMethod()] : ""),
                        "delivery_type" => $deliveryType
                    );
                }


            }
            unset($cRates);
            unset($rate);
        }
        $methodToFind = array();
        $cost = array();
        foreach ($allMethodsByCode as $code => $methodDataArr) {
            foreach ($methodDataArr as $methodData) {
                $vendorId = $methodData['vendor_id'];
                $methodToFind[$code][$vendorId] = $vendorId;
                $extraCharge = 0;
                $costVal = $methodData['cost'];
                $extraCharge = (int)Mage::getStoreConfig('carriers/'.$methodData["delivery_type"].'/cod_extra_charge');
                $deliveryVolumeLimit = (int)Mage::getStoreConfig('carriers/'.$methodData["delivery_type"].'/delivery_volume_limit');
                if (!empty($deliveryVolumeLimit)) {
                    if ($sumVolume > $deliveryVolumeLimit) {

                        unset($methodsByCode[$code]);
                        $this->clearDelivery($code);
                    }
                }
                if($extraCharge && Mage::getSingleton('checkout/session')->getPayment()['method'] == 'cashondelivery') {
                    $costVal = $costVal + $extraCharge;
                }
                $cost[$code][] = $costVal;

            }
        }

        //Find intersecting method for all vendors
        $allVendorsMethod = array();
        foreach ($methodToFind as $method => $vendorsInMethod) {
            $diff = array_diff($vendors, $vendorsInMethod);
            if (empty($diff)) {
                $allVendorsMethod[] = $method;
            }
        }

        // array(
        //		vendorId=>array(
        //			method_code=>price,
        //			....
        //		),
        // ...)
        $vendorCosts = array();
        foreach($allMethodsByCode as $rateCode=>$rateArray) {
            foreach($rateArray as $rate) {
                $vendorId = $rate['vendor_id'];
                if(!isset($vendorCosts[$vendorId])) {
                    $vendorCosts[$vendorId] = array();
                }
                $vendorCosts[$vendorId][$rate['code']] = (float)$rate['cost'];
            }
        }

        return (object)array(
            'rates' => $rates,
            'allVendorsMethod' => $allVendorsMethod,
            'vendors' => $vendors,
            'methods' => $methodsByCode,
            'cost' => $cost,
            'vendorCosts'=> $vendorCosts
        );

    }

    /**
     * @return bool
     */
    public function getHasDefaultPayment() {
        return is_array($this->getQuote()->getCustomer()->getLastUsedPayment());
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function asJson($data) {
        return Mage::helper('core')->jsonEncode($data);
    }
    /**
     * Has customer any address?
     * @return type
     */
    public function hasCustomerAddress() {
        return (bool)$this->getQuote()->getCustomer()->getAddressesCollection()->count();
    }

    /**
     * Has customer any address?
     * @return type
     */
    public function getCustomerAddressesJson() {
        $addresses = array();
        $collection = $this->getQuote()->getCustomer()->getAddressesCollection();
        foreach($collection as $address) {
            /* @var $address Mage_Customer_Model_Address */
            $arr = $address->getData();
            $arr['street'] = $address->getStreet();
            $addresses[] = $arr;
        }
        return Mage::helper("core")->jsonEncode($addresses);
    }

    /**
     * @return string
     */
    public function getStoreDefaultCountryId() {
        $countryId = Mage::app()->getStore()->getConfig("general/country/default");
        $locker = $this->getInpostLocker();
        if ($locker->getId()) {
            $countryId = $locker->getCountryId();
        }
        return $countryId;
    }

    /**
     * @return string
     */
    public function getPlaceUrl() {
        return $this->getUrl("*/*/saveOrder");
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
    public function getRates() {
        $q = Mage::getSingleton('checkout/session')->getQuote();
        $a = $q->getShippingAddress();

        $qRates = $a->getGroupedAllShippingRates();
        /**
         * Fix rate quto query
         */
        if(!$qRates) {
            $a->setCountryId(Mage::app()->getStore()->getConfig("general/country/default"));
            $a->setCollectShippingRates(true);
            $a->collectShippingRates();
            $qRates = $a->getGroupedAllShippingRates();
        }

        return $qRates;
    }


    /**
     * @param $udropshipMethod  example udtiership_1
     * @return Varien_Object
     */
    public function getOmniChannelMethodInfoByMethod($udropshipMethod)
    {
        $storeId = Mage::app()->getStore()->getId();
        return Mage::helper("udropship")->getOmniChannelMethodInfoByMethod($storeId, $udropshipMethod);
    }
}
