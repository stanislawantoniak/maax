<?php

class Zolago_Modago_Block_Checkout_Onepage_Shared_Shippingpayment_Shipping
    extends Zolago_Modago_Block_Checkout_Onepage_Abstract
{


    public function getItems()
    {
        $rates = array();

        $methodsByCode = array();

        $qRates = $this->getRates();
        $allMethodsByCode = array();
        $vendors = array();
        foreach ($qRates as $cCode => $cRates) {
            foreach ($cRates as $rate) {

                $deliveryType = "";
                $deliveryTypeModel = Mage::getModel("udtiership/deliveryType")->load($rate->getMethod());
                if ($deliveryTypeModel->getId()) {
                    $deliveryType = $deliveryTypeModel->getDeliveryCode();
                }

                $vId = $rate->getUdropshipVendor();
                if (!$vId) {
                    continue;
                }
                $rates[$vId][$cCode][] = $rate;
                $vendors[$vId] = $vId;
                $methodsByCode[$rate->getCode()] = array(
                    'vendor_id' => $vId,
                    'code' => $rate->getCode(),
                    'carrier_title' => $rate->getData('carrier_title'),
                    'method_title' => $rate->getData('method_title'),
                    "delivery_type" => $deliveryType
                );
                $allMethodsByCode[$rate->getCode()][] = array(
                    'vendor_id' => $vId,
                    'code' => $rate->getCode(),
                    'carrier_title' => $rate->getData('carrier_title'),
                    'method_title' => $rate->getData('method_title'),
                    'cost' => $rate->getPrice(),
                    "delivery_type" => $deliveryType
                );

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

} 