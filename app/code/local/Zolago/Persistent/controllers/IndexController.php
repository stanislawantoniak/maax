<?php
require Mage::getConfig()->getModuleDir("controllers", "Mage_Persistent") . DS . "IndexController.php";

class Zolago_Persistent_IndexController extends Mage_Persistent_IndexController
{
	/**
	 * Forget CMS page
	 * @return type
	 */
	public function forgetAction() {
		if(Mage::getSingleton('customer/session')->isLoggedIn()){
			return $this->_redirect("customer/account/privacy");
		}
		if(!$this->_getHelper()->isPersistent()){
		        $url = $this->_getRefererUrl();
			$obj = Zend_Uri_Http::fromString($url);
		        $obj->addReplaceQueryParameters(array('salt'=>uniqid()));
 	                $url = $obj->getUri();
 	                return $this->getResponse()->setRedirect($url);
//			return $this->_redirectReferer();
		}
		$this->loadLayout();
		$this->renderLayout();
	}

	/**
	 * Do remove persistent
	 * @return type
	 */
	public function forgetSaveAction() {
		if(Mage::getSingleton('customer/session')->isLoggedIn()){
			return $this->_redirect("customer/account/privacy");
		}

		if(!$this->_validateFormKey() || !$this->_getHelper()->isPersistent()){
			return $this->_redirectReferer();
		}

		$session = Mage::getSingleton('core/session');
		/* @var $session Mage_Core_Model_Session */

		// Do remove presisten quote
		Mage::getSingleton('persistent/observer')->setQuoteGuest();

		$session->addSuccess($this->__("Your persistent has been cleared"));
		return $this->getResponse()->setRedirect('/?salt='.uniqid());
	}

}
