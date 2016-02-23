<?php

class Zolago_Modago_Helper_Checkout extends Mage_Core_Helper_Abstract
{
    /**
     * @return array
     */
    public function getShippingCostSummary()
    {
        $cost = array();

        $q = Mage::getSingleton('checkout/cart')->getQuote();
        $totalItemsInCart = Mage::helper('checkout/cart')->getItemsCount();

        /*shipping_cost*/
        if($totalItemsInCart > 0){
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
        }
        return $cost;
    }

	/**
	 * @return string
	 */
	public function getFormattedShippingCostSummary() {
		$cost = $this->getShippingCostSummary();
		$costSum = 0;
		if (!empty($cost)) {
			$costSum = array_sum($cost);
		}
		/** @var Mage_Core_Helper_Data $coreHelper */
		$coreHelper = Mage::helper('core');
		$formattedCost = $coreHelper->currency($costSum, true, false);
		return $formattedCost;
	}
}
