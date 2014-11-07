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
        $this->_initLayoutMessages('catalog/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('Orders history'));

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }
    
    //{{{ 
    /**
     * Opened orders
     */
    public function processAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('My orders'));
        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }
    //}}}
	
}