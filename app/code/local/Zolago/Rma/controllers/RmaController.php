<?php

class Zolago_Rma_RmaController extends Mage_Core_Controller_Front_Action
{
	
	public function historyAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

		$this->_setNavigation();
        $this->renderLayout();

	}

	public function viewAction() {
		$session = Mage::getSingleton('customer/session');
		/* @var $session Mage_Customer_Model_Session */
		if(!$session->isLoggedIn()){
			return $this->_redirect('customer/account/login');
		}
		if(!Mage::registry("current_rma")){
			$rmaId = $this->getRequest()->getParam("id");
			$rma = Mage::getModel("urma/rma")->load($rmaId);
			/* @var $rma Zolago_Rma_Model_Rma */
			if($rma->getId() && $rma->getCustomerId()==$session->getCustomerId()){
				Mage::register("current_rma", $rma);
			}else{
				$session->addError(Mage::helper("zolagorma")->__("RMA is not available"));
				return $this->_redirect('sales/rma/history');
			}
		}
		$this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
		$this->_setNavigation();
		$this->renderLayout();
	}

	public function successAction() {
		$session = Mage::getSingleton('customer/session');
		if(!$session->isLoggedIn()){
			$session->addError(Mage::helper("zolagorma")->__("You need to login"));
			return $this->_redirect('customer/account/login');
		}
		$this->_getLastRma();
		$this->_forward('view');
	}
	
	/**
	 * @return Unirgy_Rma_Model_Rma
	 */
	protected function _getLastRma() {
		if(!Mage::registry("current_rma")){
			$lastRmaId = Mage::getSingleton('core/session')->getLastRmaId();
			if($lastRmaId){
				$item = Mage::getModel("urma/rma")->load($lastRmaId);
			}else{
				$collection = Mage::getResourceModel('urma/rma_collection');
				/* @var $collection Unirgy_Rma_Model_Mysql4_Rma_Collection */
				$collection->addFieldToFilter("customer_id", Mage::getSingleton('customer/session')->getCustomerId());
				$collection->setOrder("created_at", "desc")->getSelect()->limit(1);

				$item = $collection->getFirstItem();
			}
			$item->setJustCreated(true);
			Mage::register("current_rma", $item);
		}
		return Mage::registry("current_rma");
	}
	
	protected function _setNavigation() {
		$navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/rma/history');
        }	
	}

}