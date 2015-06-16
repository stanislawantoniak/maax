<?php


class Zolago_SalesManago_CustomerController extends Mage_Core_Controller_Front_Action
{

    public function cartAction()
    {
        $email = $this->getRequest()->getParam('email');
        $block = $this->getLayout()
            ->createBlock('zolagosalesmanago/customer_cart')
            ->setTemplate('zolagosalesmanago/customer/cart.phtml');
        $block->setEmail($email);
        echo $block->toHtml();
    }
}