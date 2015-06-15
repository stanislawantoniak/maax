<?php


class Zolago_SalesManago_CustomerController extends Mage_Core_Controller_Front_Action
{

    public function cartAction()
    {
        $smCId = $this->getRequest()->getParam('smcid');
        $block = $this->getLayout()
            ->createBlock('zolagosalesmanago/customer_cart')
            ->setTemplate('zolagosalesmanago/customer/cart.phtml');
        $block->setSmcid($smCId);
        echo $block->toHtml();exit;
    }
}