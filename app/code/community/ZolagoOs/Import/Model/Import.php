<?php

/**
 * Class ZolagoOs_Import_Model_Import
 */
abstract class ZolagoOs_Import_Model_Import
    extends Varien_Object
{
    protected $_helper;

    protected $_externalId; //vendor id

    const DIRECTORY = 'import';

    protected $_vendor;

    /**
     * ZolagoOs_Import_Model_Import_Stock constructor.
     */
    public function __construct()
    {
        $this->_vendor = $this->getExternalId();
    }

    /**
     * Import items from file
     */
    abstract protected function _import();

    /**
     * @return mixed (example: product, price, stock etc.)
     */
    abstract protected function _getImportEntityType();

    /**
     * @return mixed (xml, csv etc.)
     */
    abstract protected function _getFileExtension();

    /**
     * File name for _getPath()
     *
     * @return string
     */
    abstract function _getFileName();

    /**
     * @return ZolagoOs_Import_Helper_Data
     */
    public function getHelper()
    {
        if (!$this->_helper) {
            $this->_helper = Mage::helper("zolagoosimport");
        }
        return $this->_helper;
    }

    public function getExternalId()
    {
        if (!$this->_externalId) {
            $this->_externalId = $this->getHelper()->getExternalId();
        }
        return $this->_externalId;
    }

    public function runImport()
    {
        //1. Check vendor
        $vendorId = $this->getVendorId();
        if (empty($vendorId)) {
            $this->log("CONFIGURATION ERROR: EMPTY VENDOR ID", Zend_Log::ERR);
            return $this;
        }
        //2. Read file
        $fileName = $this->_getPath();

        if (empty($fileName)) {
            $this->log("CONFIGURATION ERROR: EMPTY PRODUCT IMPORT FILE", Zend_Log::ERR);
            return $this;
        }

        if (!file_exists($fileName)) {
            $this->log("CONFIGURATION ERROR: IMPORT FILE {$fileName} NOT FOUND", Zend_Log::ERR);
            return $this;
        }

        $this->_import();
    }

    /**
     * Returns local path to import file
     *
     * @return string
     */
    protected function _getPath()
    {
        return $this->_getFileName();
    }


    /**
     * Move processed file to another directory
     */
    protected function _moveProcessedFile()
    {
        $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
        $date = date('Y_m_d_H_i_s', $currentTimestamp);

        $fileName = $this->_getPath();

        $path = $this->getHelper()->getProcessedFilePlace()
            . DS . $this->getVendorId()
            . DS . $this->_getImportEntityType();

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $newfile = $path . DS . $date . "." . $this->_getFileExtension();


        if (!copy($fileName, $newfile)) {
            $this->log("Can not move file to processed directory", 2);
        } else {
            unlink($fileName);
        }
    }

    /**
     * @param $message
     * @param null $level
     */
    public function log($message, $level = NULL)
    {
        Mage::log($message, $level, "zolagoosimport_" . $this->_getImportEntityType() . ".log");
    }
}