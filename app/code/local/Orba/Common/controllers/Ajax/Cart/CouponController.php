<?php
require_once Mage::getModuleDir('controllers', 'Orba_Common') . DS . 'Ajax' . DS . 'CartController.php';

class Orba_Common_Ajax_Cart_CouponController extends Orba_Common_Ajax_CartController {
    
    /**
     * Adds coupon code to shopping cart. Available params:
     * - code (required)
     * - get_after (optional, default true; if true there is a forward to getAction after successfull product adding)
     * 
     * @throws Orba_Common_Exception
     */
    public function addAction()
    {
        try {
            $request = $this->getRequest();
            $code = trim($request->getParam('code', null));
            if ($code) {
                $quote = Mage::getSingleton('checkout/cart')->getQuote();
                $oldCoupon = $quote->getCouponCode();
                /** @var Orba_Common_Model_Ajax_Cart $cart */
                $cart = Mage::getSingleton('orbacommon/ajax_cart');
                $cart->saveCoupon($code);
                if ($code == $quote->getCouponCode()) {
                    $this->_setCartSuccessResponse('Discount coupon has been applied.');
                } else {
                    //analyze code

                    /* @var $zolagoSalesruleHelper Zolago_SalesRule_Helper_Data */
                    $zolagoSalesruleHelper = Mage::helper('zolagosalesrule');
                    $couponErrors = $zolagoSalesruleHelper->analyzeCouponByCustomerRequest($code);
                    $couponErrorsText = !empty($couponErrors) ? $couponErrors : 'Coupon code is invalid.';

                    $cart->saveCoupon($oldCoupon);
                    throw Mage::exception('Orba_Common', $couponErrorsText);
                }
            } else {
                throw Mage::exception('Orba_Common', 'No code has been specified.');
            }
        } catch (Exception $e) {
            $this->_processException($e);
        }
    }
    
    /**
     * Removes coupon code form shopping cart. Available params:
     * - get_after (optional, default true; if true there is a forward to getAction after successfull product adding)
     * 
     * @throws Orba_Common_Exception
     */
    public function removeAction() {
        try {
            Mage::getSingleton('orbacommon/ajax_cart')->saveCoupon('');
            $this->_setCartSuccessResponse('Discount coupon has been removed.');
        } catch (Exception $e) {
            $this->_processException($e);
        }
    }
    
}