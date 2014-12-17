<?php
abstract class Zolago_Persistent_Block_Forget_Abstract extends Mage_Core_Block_Html_Link
{
	/**
	 * 
	 */
	public function _construct() {
		parent::_construct();
		$this->setHref($this->getUrl("persistent/index/forget", array("_no_vendor"=>true)));
	}
	/**
	 * @return bool
	 */
	public function canShow() {
		return Mage::helper('persistent/session')->isPersistent() && 
			!Mage::getSingleton('customer/session')->isLoggedIn();
	}
	
	/**
	 * @return string
	 */
	protected function _toHtml() {
		if($this->canShow()){
			return parent::_toHtml();
		}
		return '';
	}
}
