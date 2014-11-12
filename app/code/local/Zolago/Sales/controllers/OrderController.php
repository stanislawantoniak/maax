<?php
require_once Mage::getConfig()->getModuleDir('controllers', 'Unirgy_Rma') .
		DS . "OrderController.php";

class Zolago_Sales_OrderController extends Unirgy_Rma_OrderController
{
    /**
     * Customer order history (overwrite title)
     */
    public function historyAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages(array('catalog/session', 'udqa/session'));
        $this->getLayout()->getBlock('head')->setTitle($this->__('Orders history'));

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }
	
	/**
	 * Fix add udqa sessions
	 * @return void
	 */
	protected function _viewAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }

        $this->loadLayout();
		// Fix add udqa sessions
        $this->_initLayoutMessages(array('catalog/session', 'udqa/session'));

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/order/history');
        }
        $this->renderLayout();
    }
    
    //{{{ 
    /**
     * Opened orders
     */
    public function processAction() {
        $this->loadLayout();
        $this->_initLayoutMessages(array('catalog/session', 'udqa/session'));
        $this->getLayout()->getBlock('head')->setTitle($this->__('Orders in the realization'));
        $this->renderLayout();
    }
    //}}}
	
}