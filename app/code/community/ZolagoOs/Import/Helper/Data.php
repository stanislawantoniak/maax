<?php

/**
 * Class ZolagoOs_Import_Helper_Data
 */
class ZolagoOs_Import_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_conf = array();
    protected $_file_source_conf = array();

    public function getExternalId()
    {
        return $this->getConfig('external_id');
    }

    public function getProcessedFilePlace()
    {
        return $this->getConfig('processed_files_folder');
    }

    public function getProductFile()
    {
        return $this->getFileSourceConfig('import_products');
    }

    public function getPriceFile()
    {
        return $this->getFileSourceConfig('import_prices');
    }

    public function getStockFile()
    {
        return $this->getFileSourceConfig('import_stock');
    }


    /**
     * @param null $field
     * @return array|mixed|string
     */
    public function getConfig($field = null)
    {
        if (!$this->_conf) {
            $this->_conf = Mage::getStoreConfig("zolagoosimport/general");
        }
        return $field ? trim($this->_conf[$field]) : $this->_conf;
    }


    /**
     * @param null $field
     * @return array|mixed|string
     */
    public function getFileSourceConfig($field = null)
    {
        if (!$this->_file_source_conf) {
            $this->_file_source_conf = Mage::getStoreConfig("zolagoosimport/file_source");
        }
        return $field ? trim($this->_file_source_conf[$field]) : $this->_file_source_conf;
    }
}