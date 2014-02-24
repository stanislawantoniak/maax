<?php

require_once Mage::getModuleDir('controllers', "Unirgy_Dropship") . DS . "VendorController.php";

class Zolago_Dropship_VendorController extends Unirgy_Dropship_VendorController {
	/**
	 * Index
	 */
	public function indexAction() {
		if (Mage::helper('udropship')->isUdpoActive() && !$this instanceof Unirgy_DropshipPo_VendorController) {
			$session = $this->_getSession();
			if($session->isOperatorMode()){
				$operator = $session->getOperator();
				if($operator->isAllowed("udpo")){
					return parent::indexAction();
				}
			}
            return $this->_forward('dashboard');
        }
		return parent::indexAction();
	}
	
	/**
	 * Dasboard - move index if possible
	 */
	public function dashboardAction(){
		$_hlp = Mage::helper('udropship');
        if ($_hlp->isUdpoActive() && !$this instanceof Unirgy_DropshipPo_VendorController) {
            $this->_forward('index', 'vendor', 'udpo');
            return;
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


