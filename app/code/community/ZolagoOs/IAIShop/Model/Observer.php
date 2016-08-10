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

    public function getPaymentMessages()
    {
        $paymentIncrementIds = array();

        $messagesCollection = Mage::getModel("ghapi/message")
                              ->getCollection();

        $messagesCollection
        ->addFieldToFilter("message", "paymentDataChanged")
        ->setOrder('po_increment_id', 'DESC')
        ->setOrder('message_id', 'ASC')
        ->setOrder('vendor_id', 'ASC');

        $messagesCollection->getSelect()->limit(self::IAISHOP_SYNC_ORDERS_BATCH);

        if ($messagesCollection->count() <= 0)
            return $paymentIncrementIds; //nothing to update


        foreach ($messagesCollection as $message) {
            if ((bool) Mage::helper('udropship')->getVendor($message->getVendorId())->getIaishopId())
                $paymentIncrementIds[$message->getVendorId()][] = $message->getPoIncrementId();
        }

        return $paymentIncrementIds;
    }

    public function getGhApiNewPayment($vendorId)
    {
        //ini_set("soap.wsdl_cache_enabled", 0);
        $orders = array();
        $connector = $this->getGHAPIConnector();
        $doLoginResponse = $connector->doLoginRequest($vendorId);

//		paymentDataChanged
    }
}