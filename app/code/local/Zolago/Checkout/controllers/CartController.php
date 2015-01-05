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
        $cost = array();

        $q = Mage::getSingleton('checkout/cart')->getQuote();
        $totalItemsInCart = Mage::helper('checkout/cart')->getItemsCount();

        /*shipping_cost*/
        if($totalItemsInCart > 0) {
            $a = $q->getShippingAddress();

            $qRates = $a->getGroupedAllShippingRates();

            /**
             * Fix rate quote query
             */
            if (!$qRates) {
                $a->setCountryId(Mage::app()->getStore()->getConfig("general/country/default"));
                $a->setCollectShippingRates(true);
                $a->collectShippingRates();
                $qRates = $a->getGroupedAllShippingRates();
            }
        }
        parent::indexAction();
    }
}
