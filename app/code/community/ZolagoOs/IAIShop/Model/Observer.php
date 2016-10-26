<?php

class ZolagoOs_IAIShop_Model_Observer
{
    protected $_apiConnector = false;
    protected $_iaiConnector = false;
    protected $_helper;

    private function _getGHAPIConnector()
    {
        if (!$this->_apiConnector) {
            $this->_apiConnector = Mage::getModel('ghapi/soap');
        }
        return $this->_apiConnector;
    }

    protected function _getIAIConnector() {
        if (!$this->_iaiConnector) {
            $this->_iaiConnector = Mage::getModel('zosiaishop/client_connector');
        }
        return $this->_iaiConnector;
    }

    protected function _sync($name,$vendor) {    
        $model = Mage::getModel($name);
        $connector = $this->_getIAIConnector();        
        $connector->setVendorId($vendor->getId());
        $model->setIaiConnector($connector);
        $model->setVendor($vendor);
        $model->setApiConnector($this->_getGHAPIConnector());
        $model->sync();
    }
    /**    
     * sync orders
     */
    protected function _syncOrders($vendor) {
        $this->_sync('zosiaishop/integrator_order',$vendor);
    }

    protected function _syncPayments($vendor) {
        $this->_sync('zosiaishop/integrator_payment',$vendor);
    }

    /**
     * sync shipments
     */
    protected function _syncShipments($vendor) {
        $this->_sync('zosiaishop/integrator_shipment',$vendor);
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
     * start sync payment
     */
    public function syncIAIPayments() {
        $this->_syncIAIAbstract('_syncPayments');
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
            try {
                $this->$funcName($vendor);
            } catch (Exception $xt) {
                Mage::logException($xt);
            }
        }
    }

    /**
     * start all
     */

    public function syncIAIShop()
    {
        $this->syncIAIOrders();
        $this->syncIAIPayments();
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
        $vendors = array();
        foreach ($vendorCollection as $vendor) {
            $vendorModel = Mage::getModel('udropship/vendor');
            $vendors[] = $vendorModel->load($vendor->getVendorId());
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