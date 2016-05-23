<?php
/**
 * Abstract for all steps
 */
abstract class Zolago_Modago_Block_Checkout_Onepage_Abstract extends Mage_Checkout_Block_Onepage_Abstract {

	/**
	 * @return GH_Inpost_Model_Locker
	 */
	public function getInpostLocker() {
		/** @var Zolago_Checkout_Helper_Data $helper */
		$helper = Mage::helper("zolagocheckout");
		$locker = $helper->getInpostLocker();
		return $locker;
	}

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

	public function getRateItems()
	{
		$rates = array();

		$methodsByCode = array();

		$qRates = $this->getRates();
		$allMethodsByCode = array();
		$vendors = array();

		$daysInTransitData = array();
		$shipping = $this->getUdropShippingMethods();

		$shippingMethods = array();

		foreach($shipping as $shippingItem){
			$daysInTransitData[$shippingItem->getMethodCode()] = $shippingItem->getDaysInTransit();
			$shippingMethods[$shippingItem->getMethodCode()] = $shippingItem->getData();

		}
		Mage::log($shippingMethods, null, "rates.log");
		Mage::log($qRates, null, "rates2.log");
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


				if(isset($shippingMethods[$rate->getMethod()])){
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
				$cost[$code][] = $methodData['cost'];
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
		foreach($allMethodsByCode as $rateCode=>$rateArray){
			foreach($rateArray as $rate){
				$vendorId = $rate['vendor_id'];
				if(!isset($vendorCosts[$vendorId])){
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
		foreach($collection as $address){
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
}
