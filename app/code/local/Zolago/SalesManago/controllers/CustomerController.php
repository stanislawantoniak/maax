<?php


class Zolago_SalesManago_CustomerController extends Mage_Core_Controller_Front_Action
{

    public function cartAction()
    {
        header("Expires: 0");
        header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header('Content-Type: text/html');

        $email  = str_replace(' ', '+', $this->getRequest()->getParam('email'));
        $block = $this->getLayout()
            ->createBlock('zolagosalesmanago/customer_cart')
            ->setTemplate('zolagosalesmanago/customer/cart.phtml');
        $block->setEmail($email);
        echo $block->toHtml();exit;
    }
}