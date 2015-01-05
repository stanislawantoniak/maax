<?php

require_once Mage::getModuleDir("controllers", "Mage_Checkout") . DS . "CartController.php";

/**
 * Shopping cart controller
 */
class Zolago_Checkout_CartController extends Mage_Checkout_CartController
{

    /**
     * Shopping cart display action
     */
    public function indexAction()
    {
        /** @var Zolago_Checkout_Helper_Data $helper */
        $helper = Mage::helper("zolagocheckout");
        $helper->fixCartShippingRates();
        parent::indexAction();
    }
}
