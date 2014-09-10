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
	
} 