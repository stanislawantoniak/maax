<?php
class Orba_Informwhenavailable_EntryController extends Mage_Core_Controller_Front_Action {

    protected function getConfig() {
        return Mage::getModel('informwhenavailable/config');
    }

    public function createAction() {
        $request = $this->getRequest();
        $customer_id = null;
        $email = null;
        if (1 || !Mage::helper('customer')->isLoggedIn()) { // allways email
            $email = $request->getParam('email');
            if (!$email) {
                return $this->logError('No valid email');
            }
        } else {
            $email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
            $customer_id = Mage::getSingleton('customer/session')->getCustomer()->getId();
        }
        if (!$product_id = $request->getParam('product_id')) {
            return $this->logError('No valid product Id');
        } else {
            $product = Mage::getModel('catalog/product')->load($product_id);
        }
        if (!$attributeValue = $request->getParam('attribute_value')) {
            return $this->logError('No valid attribute Id');
        }
        if (!$superAttribute = $request->getParam('super_attribute')) {
            return $this->logError('No valid super attribute Id');
        }
        $simpleProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes(array($superAttribute => $attributeValue),$product);
        if (Mage::getModel('informwhenavailable/entry')->isRequestAlreadySent($simpleProduct, $email)) {
            return $this->logInfo('Email saved');
        }
        try {
            $is_subscription = (bool)$request->getParam('newsletter');
            $datetime = date('Y-m-d H:i:s', time());
            $entry = Mage::getModel('informwhenavailable/entry');
            $entry->setEmail($email);
            $entry->setCustomerId($customer_id);
            $entry->setSku($simpleProduct->getSku());
            $entry->setStoreId(Mage::app()->getStore()->getId());
            $entry->setIsSubscription($is_subscription);
            $entry->setCreatedAt($datetime);
            $entry->setUpdatedAt($datetime);
            $entry->save();
            if ($is_subscription) {
                Mage::getModel('newsletter/subscriber')->subscribe($email);
            }
            return $this->logInfo('Email saved');
        } catch (Exception $e) {
            Mage::logException($e);
            return $this->logError('Internal server error');
        }
    }

    /**
     * redirect referer with salt
     */
    public function redirectWithSalt() {
        $url = $this->_getRefererUrl();
        $obj = Zend_Uri_Http::fromString($url);
        $obj->addReplaceQueryParameters(array('salt'=>uniqid()));
        $url = $obj->getUri();
        return $this->getResponse()->setRedirect($url);    
    }
    public function informAction() {
        Mage::getModel('informwhenavailable/entry')->informIfAvailable();
    }
    
    /**
     * log info into session
     */

    public function logInfo($text) {
        Mage::getSingleton('customer/session')->addSuccess(Mage::helper('wfwf')->__($text));
        return $this->redirectWithSalt();
    }
    
    /**
     * log error into serssion
     */

    public function logError($text) {
        Mage::getSingleton('customer/session')->addError(Mage::helper('wfwf')->__($text));
        return $this->redirectWithSalt();
    }

}