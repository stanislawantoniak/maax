<?php

require_once Mage::getModuleDir('controllers', "Unirgy_Dropship") . DS . "VendorController.php";

class Zolago_Dropship_VendorController extends Unirgy_Dropship_VendorController {
	/**
	 * Index
	 */
	public function indexAction() {
		if (Mage::helper('udropship')->isUdpoActive()) {
			$session = $this->_getSession();
			if($session->isOperatorMode()){
				$operator = $session->getOperator();
				if($operator->isAllowed("udpo/vendor")){
					Mage::log("1");
					return parent::indexAction();
				}else{
					Mage::log("2");
					return $this->_forward('dashboard');
				}
			}
		}
		
					Mage::log("3");
		return parent::indexAction();
	}
	
	/**
	 * Dasboard - move index if possible
	 */
	public function dashboardAction(){
        if (Mage::helper('udropship')->isUdpoActive() ) {
			$session = $this->_getSession();
			if($session->isOperatorMode()){
				$operator = $session->getOperator();
				if($operator->isAllowed("udpo/vendor")){
					return $this->_forward('index', 'vendor', 'udpo');
				}
			}
        }
		$this->_renderPage(null, "dashboard");
	}
	
	/**
	 * Denied action
	 */
	public function deniedAction(){
		die("Access denied");
	}
	
	/**
	 * Set locale
	 */
	public function setLocaleAction(){
		$locale = $this->getRequest()->getParam("locale", 
				Mage::app()->getLocale()->getLocaleCode());
		$this->_getSession()->setLocale($locale);
		return $this->_redirectReferer();
	}
}


