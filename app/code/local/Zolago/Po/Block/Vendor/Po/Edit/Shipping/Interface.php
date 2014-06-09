<?php

interface Zolago_Po_Block_Vendor_Po_Edit_Shipping_Interface {
	const MODE_GENERATE		= "generate";
	const MODE_ADD			= "add";
	
	public function isMethodChecked($code);
	public function getFormUrl();
	public function getVendor();
	public function getMode();
	public function getAvailableMethods();
	public function getRemainingShippingAmount();
	public function getRemainingWeight();
	public function getShippingMethod();
	public function getCarriers();
	public function canUseCarrier();
	public function canPosUseDhl();
}
