<?php
/**
 * Abstract for all steps
 */
abstract class Zolago_Modago_Block_Checkout_Onepage_Abstract extends Mage_Checkout_Block_Onepage_Abstract {
	
	/**
	 * @return Varien_Object
	 */
	public function getInpostLocker() {
		/** @var Zolago_Checkout_Helper_Data $helper */
		$helper = Mage::helper("zolagocheckout");
		$locker = $helper->getInpostLocker();
		return $locker;
	}

	public function getDeliveryDays() {
		return "1-2"; // TODO
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
        return Mage::app()->getStore()->getConfig("general/country/default");
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
