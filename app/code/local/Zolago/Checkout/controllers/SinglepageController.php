<?php

class Zolago_Checkout_SinglepageController extends Zolago_Checkout_Controller_Abstract
{

    /**
     * Index action for logged users
     * @return type
     */
    public function indexAction()
    {

        // Not logged user - redirect to guest controller
        if (!$this->_getCustomerSession()->isLoggedIn()) {
            Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure' => true)));
            return $this->_redirect("*/guest/login");
        }

        // Display checkout page for loged in
        parent::indexAction();
    }

    /**
     * Predispatch: should set layout area
     *
     * @return Mage_Checkout_OnepageController
     */
    public function preDispatch()
    {
        Mage_Core_Controller_Front_Action::preDispatch();
        /**
         * I dont want to be redirected to address
         * when I want to place and order on new account (without address)
         * details: while clicking 'kupuję' in cart we're getting redirected
         * to my account page with errors that say you don't have name and lastname
         * $this->_preDispatchValidateCustomer();
         */

        $checkoutSessionQuote = Mage::getSingleton('checkout/session')->getQuote();
        if ($checkoutSessionQuote->getIsMultiShipping()) {
            $checkoutSessionQuote->setIsMultiShipping(false);
            $checkoutSessionQuote->removeAllAddresses();
        }

        if (!$this->_canShowForUnregisteredUsers()) {
            $this->norouteAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return;
        }

        return $this;
    }
}
