<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_View extends Magpleasure_Common_Model_Abstract
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('mpblog/view');
    }

    /**
     * Customer Session
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    public function registerMe(Mage_Core_Controller_Request_Http $request, $refererUrl = null)
    {
        $this->getResource()->loadByPostAndSession($this, $request->getParam('id'), $this->_getCustomerSession()->getSessionId());
        if (!$this->getId()){

            try {

                /* @var $helper Mage_Core_Helper_Http */
                $helper = Mage::helper('core/http');
                $now = new Zend_Date();
                $this
                    ->setPostId($request->getParam('id'))
                    ->setCustomerId($this->_getCustomerSession()->isLoggedIn() ? $this->_getCustomerSession()->getCustomerId() : null)
                    ->setSessionId($this->_getCustomerSession()->getSessionId())
                    ->setRemoteAddr($helper->getRemoteAddr(true))
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->setCreatedAt($now->toString(Varien_Date::DATETIME_INTERNAL_FORMAT))
                    ->setRefererUrl($refererUrl)
                    ->save()
                ;

            } catch (Exception $e){

                # Do nothing
            }
        }
        return $this;
    }
}