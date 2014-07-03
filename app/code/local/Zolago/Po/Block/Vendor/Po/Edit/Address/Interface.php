<?php

interface Zolago_Po_Block_Vendor_Po_Edit_Address_Interface {
	//put your code here
	public function getFormUrl();
	public function isSameAsOrigin($type);
	public function isBilling();
	public function isShipping();
	public function getType();
}
