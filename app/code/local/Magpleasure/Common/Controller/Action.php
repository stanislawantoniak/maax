<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Controller_Action extends Mage_Core_Controller_Front_Action
{
    /**
     * Customer Session
     *
     * @return Mage_Customer_Model_Session
     */
    public function getCustomerSession()
    {
        return Mage::getSingleton("customer/session");
    }

    /**
     * Response for Ajax Request
     *
     * @param array $result
     */
    protected function _ajaxResponse($result = array(), $responseCode = 200)
    {
        $this->getResponse()->setHttpResponseCode($responseCode);
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     * Retrieves Messages Html
     *
     * @return string
     */
    protected function _getMessageBlockHtml()
    {
        return $this->getLayout()->getMessagesBlock()->addMessages(Mage::getSingleton('customer/session')->getMessages(true))->toHtml();
    }
}