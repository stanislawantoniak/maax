<?php

class ZolagoOs_IAIShop_Model_Observer
{
    private $_connector = false;
    protected $_token = array();
    protected $_helper;

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

	protected function _syncPayments($vendor) {
		$model = Mage::getModel('zosiaishop/integrator_payment');
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
            $this->fileLog('No messages for IAI Shop');
            return false;
        }

        foreach ($vendors as $vendor) {
            $this->_syncOrders($vendor);
			$this->_syncPayments($vendor);
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

    public function getHelper() {
        if (!$this->_helper) {
            $this->_helper = Mage::helper('zosiaishop');
        }
        return $this->_helper;
    }

    public function fileLog($mess) {
        $this->getHelper()->fileLog($mess);
    }
}