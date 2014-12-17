<?php
class Zolago_Persistent_Block_Forget_Footerlink extends Mage_Core_Block_Html_Link
{
	public function _construct() {
        $this->setTemplate('zolagopersistent/forget/footerlink.phtml');
		$this->setHref($this->getUrl("persistent/index/forget", array("_no_vendor"=>true)));
	}
	
	public function getAnchorText() {
		return $this->__("Forget me");
	}
	
	public function canShow() {
		return Mage::helper('persistent/session')->isPersistent() && !Mage::getSingleton('customer/session')->isLoggedIn();
	}
	
	protected function _toHtml() {
		if($this->canShow()){
			return parent::_toHtml();
		}
		return '';
	}
}
