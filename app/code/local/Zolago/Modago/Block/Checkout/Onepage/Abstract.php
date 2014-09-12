<?php
/**
 * Abstract for all steps
 */
abstract class Zolago_Modago_Block_Checkout_Onepage_Abstract 
	extends Mage_Checkout_Block_Onepage_Abstract
{
	/**
	 * Has customer any address?
	 * @return type
	 */
	public function hasCustomerAddress() {
		return (bool)$this->getQuote()->getCustomer()->getAddressCollection()->count();
	}
	
	/**
	 * @return type
	 */
	public function getStoreDefaultCountryId() {
		return "PL";Mage::app()->getStore()->getConfig("general/country/default");
	}
	
	/**
	 * @return string
	 */
	public function getPlaceUrl() {
		return $this->getUrl("*/*/saveOrder");
	}
	
} 