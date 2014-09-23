<?php
require_once Mage::getConfig()->getModuleDir('controllers', 'Mage_Sales') . DS . "OrderController.php";
die("TAL");
class Zolago_Sales_OrderController extends Mage_Sales_OrderController
{


    /**
     * Customer order history
     */
    public function historyAction()
    {
		die("TAK");
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('My Orders'));

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }

}
