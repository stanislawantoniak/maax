<?php
class Zolago_Po_Block_Vendor_Po_Edit_Inpostaddress
	extends Zolago_Po_Block_Vendor_Po_Edit_Abstract
{
	public function getInpostLocker() {
		/** @var Zolago_Checkout_Helper_Data $helper */
		$helper = Mage::helper("zolagocheckout");
		$locker = $helper->getInpostLocker();
		return $locker;
	}
}
