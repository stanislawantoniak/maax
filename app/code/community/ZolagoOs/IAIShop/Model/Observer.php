<?php

class ZolagoOs_IAIShop_Model_Observer
{
    private $_connector = false;
    protected $_token = array();

    private function _getGHAPIConnector()
    {
        if (!$this->_connector) {
            $this->_connector = Mage::getModel('ghapi/soap');
        }
        return $this->_connector;
    }
    
    
    /**
     * sync orders
     */
    protected function _syncOrders($vendor) {
        $model = Mage::getModel('zosiaishop/integrator_order');
        $model->setVendor($vendor);
        $model->setConnector($this->_getGHAPIConnector());
        $model->sync();
    }
    /**
     * start process
     */


    public function syncIAIShop()
    {

        $vendors = $this->getAllowVendors();
        if (!count($vendors)) {
            return false;
        }        

        foreach ($vendors as $vendor) {
            $this->_syncOrders($vendor);
        }
        
    }
    
    
    /**
     * prepare list of messages to confirmation
     *
     * @param ZolagoOs_OmniChannel_Model_Vendor $vendor
     * @param StdClass $order
     */

    public function setConfirmMessage($vendor,$order) {
        $this->_confirm[$vendor->getId()][] = $order->messageID;
    }
    /**
     * confirm messages list in api queue
     *
     * @param ZolagoOs_OmniChannel_Model_Vendor $vendor
     */
    public function confirmMessage($vendor) {
        if (!isset($this->_confirm[$vendor->getId()])) {
            return;
        }
        $connector = $this->_getGHAPIConnector();
        $token = $this->_getToken($vendor);
        $params = new StdClass();
        $params->sessionToken = $token;
        $params->messageID = new StdClass();
        $params->messageID->ID = $this->_confirm[$vendor->getId()];
        $connector->setChangeOrderMessageConfirmation($params);
    }

    /**
     * @return array
     */
    public function getAllowVendors()
    {
        $orderIncrementIds = array();

        $messagesCollection = Mage::getModel("ghapi/message")
            ->getCollection();
        $messagesCollection
            ->addFieldToFilter("message", "newOrder")
            ->addFieldToSelect('vendor_id')
            ->getSelect()->group('vendor_id');

        
        if ($messagesCollection->count() <= 0)
            return $orderIncrementIds; //nothing to update
        $vendors = array();
        foreach ($messagesCollection as $message) {
            $vendor = Mage::getModel('udropship/vendor')->load($message->getVendorId());
            if ($vendor->getData('zosiaishop_vendor_access_allow')) {
                $vendors[] = $vendor;
            }
        }
        
        return $vendors;
    }

    
    
}