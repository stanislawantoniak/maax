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

    /**
     * Import items from file
     */
    abstract protected function _import();

    /**
     * Returns local path to import file
     *
     * @return string
     */
    protected function _getPath()
    {
        return $this->_getFileName();
        //return (!empty($this->_getFileName())) ? Mage::getBaseDir('var') . DS . self::DIRECTORY . DS . $this->_getFileName() : "";
    }

    /**
     * File name for _getPath()
     *
     * @return string
     */
    protected function _getFileName()
    {
        return $this->getHelper()->getProductFile();

    }

    /**
     * @param $message
     */
    public function log($message)
    {
        Mage::log($message, null, "zolagoosimport.log");
    }
}