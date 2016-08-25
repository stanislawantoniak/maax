<?php

class ZolagoOs_IAIShop_Model_Observer
{
    protected $_connector = false;
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
     * sync shipments
     */
    protected function _syncShipments($vendor) {
        $model = Mage::getModel('zosiaishop/integrator_shipment');
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