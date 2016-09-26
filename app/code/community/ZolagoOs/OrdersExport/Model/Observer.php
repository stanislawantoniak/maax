<?php

/**
 * Class ZolagoOs_OrdersExport_Model_Observer
 */
class ZolagoOs_OrdersExport_Model_Observer
{

    protected $_apiConnector = false;
    protected $_helper;

    /**
     * @return bool|GH_Api_Model_Soap
     */
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
    }


    public function exportOrders()
    {
        $this->_exportAbstract('_exportOrders');
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
        $this->_export('zosordersexport/integrator_order');
    }

    protected function _export($name)
    {
        //1. Check vendor
        $vendorId = $this->getHelper()->getExternalId();
        if (empty($vendorId)) {
            $this->fileLog("CONFIGURATION ERROR: EMPTY VENDOR ID", Zend_Log::ERR);
            return $this;
        }

        //2. Check export directory
        $exportDirectory = $this->getHelper()->getExportDirectory();

        if (empty($exportDirectory)) {
            $this->fileLog("CONFIGURATION ERROR: EMPTY EXPORT DIRECTORY FIELD", Zend_Log::ERR);
            return $this;
        }

        //3. Run export
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