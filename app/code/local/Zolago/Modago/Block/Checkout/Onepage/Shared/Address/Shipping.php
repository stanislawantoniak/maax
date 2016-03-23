<?php

class Zolago_Modago_Block_Checkout_Onepage_Shared_Address_Shipping
	extends Zolago_Modago_Block_Checkout_Onepage_Abstract
{
	protected $shippingAddress = null;

    public function getOrderSomeoneElseFlag()
    {
        $quote = $this->getQuote();
        $billing = $quote->getBillingAddress();
        $shipping = $quote->getShippingAddress();
        $flag = false;
		
		// If address is not filled - not inited - same address data
		if($shipping->getFirstname()===null && 
		   $shipping->getLastname()===null && 
		   $shipping->getTelephone()===null){
			return false;
		}
		
        if ($quote->getCustomerFirstname() !== $shipping->getFirstname()
            || $quote->getCustomerLastname() !== $shipping->getLastname()
            || $billing->getTelephone() !== $shipping->getTelephone()) {
            $flag = true;
        }

        return $flag;
	}

	public function getShippingAddress() {
		if (is_null($this->shippingAddress)) {
			$this->shippingAddress = $this->getQuote()->getShippingAddress();
		}
		return $this->shippingAddress;
	}

	public function getShippingAddressId() {
		$shippingAddress = $this->getShippingAddress();
		return $shippingAddress->getId();
	}

	public function getRegionId() {
		$object = $this->getInpostLocker();
		if (!$object->getId()) {
			$object = $this->getShippingAddress();
		}
		return $this->escapeHtml($object->getRegionId());
	}

	public function getRegion() {
		$object = $this->getInpostLocker();
		if (!$object->getId()) {
			$object = $this->getShippingAddress();
		}
		return $this->escapeHtml($object->getRegion());
	}

	public function getFax() {
		$object = $this->getInpostLocker();
		if (!$object->getId()) {
			$object = $this->getShippingAddress();
		}
		return $this->escapeHtml($object->getFax());
	}

	public function getCountryId() {
		$object = $this->getInpostLocker();
		if (!$object->getId()) {
			$object = $this->getShippingAddress();
		}
		return $object->getCountryId() ? $object->getCountryId() : $this->getStoreDefaultCountryId();
	}

	public function getSaveInAddressBook() {
		$object = $this->getInpostLocker();
		if (!$object->getId()) {
			return 1;
		}
		return 0;
	}

	public function getSameAsBilling() {
		$object = $this->getInpostLocker();
		if (!$object->getId()) {
			$sameAsBilling = (int)$this->getShippingAddress()->getSameAsBilling();
			return $sameAsBilling;
		}
		return 0;
	}
} 