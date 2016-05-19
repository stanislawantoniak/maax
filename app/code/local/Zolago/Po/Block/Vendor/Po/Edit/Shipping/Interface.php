<?php

interface Zolago_Po_Block_Vendor_Po_Edit_Shipping_Interface {
	const MODE_GENERATE		= "generate";
	const MODE_ADD			= "add";
	
	public function getFormUrl();
	public function getVendor();
	public function getMode();
	public function getShippingMethod();
}
