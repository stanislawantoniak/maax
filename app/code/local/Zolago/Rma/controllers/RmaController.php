<?php

class Zolago_Rma_RmaController extends Mage_Core_Controller_Front_Action
{
	
	public function indexAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/rma');
        }
        $this->renderLayout();

	}



}