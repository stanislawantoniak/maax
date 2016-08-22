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


    abstract protected function _getImportEntityType();

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
     * File name for _getPath()
     *
     * @return string
     */
    abstract function _getFileName();

    /**
     * @param $message
     * @param null $level
     */
    public function log($message, $level = NULL)
    {
        Mage::log($message, $level, "zolagoosimport_" . $this->_getImportEntityType() . ".log");
    }
}