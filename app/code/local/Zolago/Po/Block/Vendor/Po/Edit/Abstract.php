<?php
abstract class Zolago_Po_Block_Vendor_Po_Edit_Abstract
	extends Mage_Core_Block_Template
{
	public function getPo() {
		return $this->getParentBlock()->getPo();
	}
	
	public function getPoUrl($action, $params=array()) {
		return $this->getParentBlock()->getPoUrl($action, $params);
	}
	
	public function getVendor() {
		return $this->getParentBlock()->getVendor();
	}
	
}
