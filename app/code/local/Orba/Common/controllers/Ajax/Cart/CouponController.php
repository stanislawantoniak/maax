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
            $code = $request->getParam('code', null);
            if ($code) {
                //analyze code
                $res = Zolago_SalesRule_Helper_Data::analyzeCouponByCustomerRequest($code);
                if (!empty($res)) {
                    throw Mage::exception('Orba_Common', $res);
                }

                $quote = Mage::getSingleton('checkout/cart')->getQuote();
                $oldCoupon = $quote->getCouponCode();
                $cart = Mage::getSingleton('orbacommon/ajax_cart');
                $cart->saveCoupon($code);
                if ($code == $quote->getCouponCode()) {
                    $this->_setCartSuccessResponse('Discount coupon has been applied.');
                } else {
                    //check if coupon does not meet conditions
                    $couponM = Mage::getModel('salesrule/coupon');
                    $couponM->load($code, 'code');
                    $salesRuleId = $couponM->getRuleId();
                    $couponS = Mage::getModel('salesrule/rule');
                    $couponS->load($salesRuleId);

                    if ($couponS->getId()) {

                        throw Mage::exception('Orba_Common', Mage::helper('zolagomodago')->__('The coupon does meet conditions') . ': ' . $couponS->getDescription());
                    }
                    $cart->saveCoupon($oldCoupon);
                    throw Mage::exception('Orba_Common', 'Coupon code is invalid.');
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