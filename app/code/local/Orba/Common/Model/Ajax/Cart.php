<?php

class Orba_Common_Model_Ajax_Cart extends Mage_Core_Model_Abstract {
    
    /**
     * Gets data of shopping cart and its items
     * 
     * @return array
     */
    public function getCartData() {
        $_helper = Mage::helper('checkout/cart');
        $quote = $_helper->getQuote();
        $data = array(
            'count' => $_helper->getSummaryCount() ? $_helper->getSummaryCount() : 0,
            'amount' => $quote->getGrandTotal() ? $quote->getGrandTotal() : 0,
            'items' => array()
        );
        $items = $quote->getAllItems();
        foreach ($items as $item) {
            // For configurable products add only options and not parent products
            if ($item->getProductType() === 'configurable') {
                continue;
            }
            $data['items'][] = $item->getData();
        }
        return $data;
    }
    
    /**
     * Adds product to the shopping cart
     * 
     * @param int $productId
     * @param double $qty
     * @param array $superAttribute
     * @throws Orba_Common_Exteption
     */
    public function addProduct($productId, $qty, $superAttribute) {
        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId()) {
            throw Mage::exception('Orba_Common', 'Product does not exist.');
        }
        if (!$qty) {
            throw Mage::exception('Orba_Common', 'Product quantity has not been specified.');
        }
        if ($qty < 0) {
            throw Mage::exception('Orba_Common', 'Product quantity must be greater than zero.');
        }
	    /** @var Mage_Checkout_Model_Cart $cart */
        $cart = Mage::getSingleton('checkout/cart');
        $params = array(
            'product' => $productId,
            'qty' => $qty
        );
        if ($superAttribute) {
            $params['super_attribute'] = $superAttribute;
        }
        $cart->addProduct($product, $params);
        $cart->save();
        /** @var Zolago_Checkout_Helper_Data $helper */
        $helper = Mage::helper('zolagocheckout');
        $helper->fixCartShippingRates();  
                   
        $cart->save();  
                               
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
    }
    
    /**
     * Updates shopping cart item quantity
     * 
     * @param int $itemId
     * @param double $qty
     * @throws Orba_Common_Exception
     */
    public function updateItem($itemId, $qty) {
        if (!$qty) {
            throw Mage::exception('Orba_Common', 'Item quantity has not been specified.');
        }
        if ($qty < 0) {
            throw Mage::exception('Orba_Common', 'Item quantity must be greater than zero.');
        }
        $cart = Mage::getSingleton('checkout/cart');
        $items = $cart->getItems();
        $itemFound = false;
        foreach ($items as $item) {
            if ($item->getId() == $itemId) {
                $itemFound = true;
                $item->setQty($qty);
                break;
            }
        }
        if ($itemFound) {
            $cart->save();
            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        } else {
            throw Mage::exception('Orba_Common', 'Item does not exist.');
        }
    }
    
    /**
     * Removes item from shopping cart
     * 
     * @param int $itemId
     * @throws Orba_Common_Exception
     */
    public function removeItem($itemId) {
        $cart = Mage::getSingleton('checkout/cart');
        $items = $cart->getItems();
        $itemFound = false;
        foreach ($items as $item) {
            if ($item->getId() == $itemId) {
                $itemFound = true;
                break;
            }
        }
        if ($itemFound) {
            $cart->removeItem($itemId)->save();
            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        } else {
            throw Mage::exception('Orba_Common', 'Item does not exist.');
        }
    }
    
    /**
     * Saves coupon to quote using standard Magento way
     * 
     * @param string $code
     */
    public function saveCoupon($code) {
        $quote = Mage::getSingleton('checkout/cart')->getQuote();
        $quote->setCouponCode($code)
                ->collectTotals()
                ->save();
    }
    
}