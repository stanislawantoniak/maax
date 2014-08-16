<?php

class Orba_Common_Ajax_CartController extends Orba_Common_Controller_Ajax {
    
    /**
     * Gets data of shopping cart and its items and set it to response body in JSON format
     */
    public function getAction() {
        try {
            $result = $this->_generateGetResponse();
            if ($message = $this->_getFlashMessage()) {
                $result['content']['message'] = $message;
            }
            $this->_setSuccessResponse($result);
        } catch (Exception $e) {
            $this->_processException($e);
        }
    }
    
    /**
     * Adds product to the shopping cart. Available params:
     * - product_id (required if sku left blank)
     * - sku (required if product_id left blank)
     * - qty (required)
     * - super_attribute (required for configurable products)
     * - get_after (optional, default true; if true there is a forward to getAction after successfull product adding)
     * 
     * @throws Orba_Common_Exception
     */
    public function addAction() {
        try {
            $request = $this->getRequest();
            $productId = $this->_getProductIdFromRequest();
            if ($productId) {
                $qty = $request->getParam('qty', null);
                $superAttribute = $request->getParam('super_attribute', array());
                Mage::getSingleton('orbacommon/ajax_cart')->addProduct($productId, $qty, $superAttribute);
                $this->_setCartSuccessResponse('Product has been added to the shopping cart.');
            } else {
                throw Mage::exception('Orba_Common', 'No product has been specified.');
            }
        } catch (Exception $e) {
            $this->_processException($e);
        }
    }
    
    /**
     * Updates shopping cart item quantity. Available params:
     * - item_id (required)
     * - qty (required)
     * - get_after (optional, default true; if true there is a forward to getAction after successfull product adding)
     * 
     * @throws Orba_Common_Exception
     */
    public function updateAction() {
        try {
            $request = $this->getRequest();
            $itemId = $request->getParam('item_id', null);
            if ($itemId) {
                $qty = $request->getParam('qty', null);
                Mage::getSingleton('orbacommon/ajax_cart')->updateItem($itemId, $qty);
                $this->_setCartSuccessResponse('Shopping cart item has been updated.');
            } else {
                throw Mage::exception('Orba_Common', 'No item has been specified.');
            }
        } catch (Exception $e) {
            $this->_processException($e);
        }
    }
    
    /**
     * Removes item form shopping cart. Available params:
     * - item_id (required)
     * - get_after (optional, default true; if true there is a forward to getAction after successfull product adding)
     * 
     * @throws Orba_Common_Exception
     */
    public function removeAction() {
        try {
            $request = $this->getRequest();
            $itemId = $request->getParam('item_id', null);
            if ($itemId) {
                Mage::getSingleton('orbacommon/ajax_cart')->removeItem($itemId);
                $this->_setCartSuccessResponse('Shopping cart item has been removed.');
            } else {
                throw Mage::exception('Orba_Common', 'No item has been specified.');
            }
        } catch (Exception $e) {
            $this->_processException($e);
        }
    }

    public function applycouponAction()
    {
        $response = array();
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $response['error_message'] = Mage::helper('zolagomodago')->__("There is no items in your cart.");
            $response['status'] = false;
            $this->_setSuccessResponse($response);
            return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $response['error_message'] = Mage::helper('zolagomodago')->__("There is nothing to apply.");
            $response['status'] = false;
            return;
        }

        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;

            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
                ->collectTotals()
                ->save();

            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $this->_getQuote()->getCouponCode()) {
                    $response['label'] = $couponCode;
                    $this->_formatSuccessContentForResponse($response);
                } else {
                    $response['error_message'] = Mage::helper('zolagomodago')->__('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($couponCode));
                    $response['status'] = false;
                    $this->_setSuccessResponse($response);
                    return ;
                }
            } else {
                $response['message'] = Mage::helper('zolagomodago')->__('Coupon code was canceled.');
            }

            $this->_formatSuccessContentForResponse($response);
        } catch (Mage_Core_Exception $e) {
            $response['error_message'] = Mage::helper('zolagomodago')->__('Cannot apply the coupon code.');
            $response['status'] = false;
        } catch (Exception $e) {
            $response['error_message'] = Mage::helper('zolagomodago')->__('Cannot apply the coupon code.');
            $response['status'] = false;
        }

        $this->_setSuccessResponse($response);
        return ;
    }
    
    /**
     * Geneartes response array for get method
     * 
     * @return array
     */
    protected function _generateGetResponse() {
        $data = Mage::getSingleton('orbacommon/ajax_cart')->getCartData();
        $result = $this->_formatSuccessContentForResponse($data);
        return $result;
    }
    
    /**
     * Prepares JSON response with results of add, update and remove actions.
     * If get_after param is set to true, the whole shopping cart content will be added to the response. 
     * 
     * @param string $message
     */
    protected function _setCartSuccessResponse($message) {
        $getAfter = $this->getRequest()->getParam('get_after', true);
        if ($getAfter) {
            $this->_setFlashMessage($this->__($message));
            $this->_forward('get');
        } else {
            $result = $this->_generateBasicSuccessResponse($message);
            $this->_setSuccessResponse($result);
        }
    }

    public function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    public function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }
    
}