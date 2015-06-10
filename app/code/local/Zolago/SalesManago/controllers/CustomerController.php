<?php



class Zolago_SalesManago_CustomerController extends Mage_Core_Controller_Front_Action {

    public function cartAction(){
        echo "Test  cartAction: email = ";
        echo $this->getRequest()->getParam('email');
    }
}