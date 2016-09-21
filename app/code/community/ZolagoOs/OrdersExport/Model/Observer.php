<?php

/**
 * Class ZolagoOs_OrdersExport_Model_Observer
 */
class ZolagoOs_OrdersExport_Model_Observer
{

    protected $_apiConnector = false;
    protected $_helper;

    private function _getGHAPIConnector()
    {
        if (!$this->_apiConnector) {
            $this->_apiConnector = Mage::getModel('ghapi/soap');
        }
        return $this->_apiConnector;
    }

    /**
     * Export All
     */
    public function cronExport()
    {
        $this->exportOrders();
        $this->exportItems();
        $this->exportCustomers();
    }


    public function exportOrders()
    {
        $this->_exportAbstract('_exportOrders');
    }

    public function exportItems()
    {
        $this->_exportAbstract('_exportItems');
    }

    public function exportCustomers()
    {
        $this->_exportAbstract('_exportCustomers');
    }


    /**
     * @param $functionName
     */
    protected function _exportAbstract($functionName)
    {
        try {
            $this->$functionName();
        } catch (Exception $xt) {
            Mage::logException($xt);
        }
    }

    protected function _exportOrders()
    {
        $this->_export('zosordersexport/export_order');
    }

    protected function _export($name)
    {
        $vendorId = 1; // @todo Boooo hardcode
        $vendor = Mage::getModel("udropship/vendor")->load($vendorId);
        $model = Mage::getModel($name);
        $model->setVendor($vendor);
        $model->setApiConnector($this->_getGHAPIConnector());
        $model->sync();
    }

    public function getHelper()
    {
        if (!$this->_helper) {
            $this->_helper = Mage::helper('zosordersexport');
        }
        return $this->_helper;
    }

    public function fileLog($mess)
    {
        $this->getHelper()->fileLog($mess);
    }

}