<?php

class Zolago_Rma_RmaController extends Mage_Core_Controller_Front_Action
{
	
	public function historyAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

		$this->_setNavigation();
        $this->renderLayout();

	}


	public function successAction() {
		if(!Mage::getSingleton('customer/session')->isLoggedIn()){
			return $this->_redirect('customer/account/login');
		}
		$this->_getLastRma();
		$this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
		$this->_setNavigation();
		$this->renderLayout();
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