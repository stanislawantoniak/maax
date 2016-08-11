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
    
    /**
     * start sync orders
     */

    public function syncIAIOrders() {
        $this->_syncIAIAbstract('_syncOrders');
    }
    
    /**
     * start sync shipment
     */
    public function syncIAIShipments() {	
        $this->_syncIAIAbstract('_syncShipments');
    }
    /**
     * synchronize abstract
     *
     * @params string $funcName method name
     */
    protected function _syncIAIAbstract($funcName) {
        $vendors = $this->getAllowVendors();
        if (!count($vendors)) {
            $this->fileLog('No allowed vendors');            
            return false;
        }
        foreach ($vendors as $vendor) {
            $this->$funcName($vendor);
        }        
    }
    
    /**
     * sync shipments
     */
    protected function _syncShipments($vendor) {
        $model = Mage::getModel('zosiaishop/integrator_shipment');
        $model->setVendor($vendor);
        $model->setConnector($this->_getGHAPIConnector());
        $model->sync();        
    }
    /**
     * start all 
     */

    public function syncIAIShop()
    {
        $this->syncIAIOrders();
        $this->syncIAIShipments();
    }

    /**
     * @return array
     */
    public function getAllowVendors()
    {
        $orderIncrementIds = array();
        $vendorModel = Mage::getModel('udropship/vendor');
        $vendorCollection = $vendorModel->getCollection();
        $vendorCollection->addFieldToFilter('custom_vars_combined',array('like' => '%"zosiaishop_vendor_access_allow":"1"%'))
            ->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('vendor_id');
        foreach ($vendorCollection as $vendor) {            
                $vendors[] = $vendorModel->load($vendor->getVendorId());
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