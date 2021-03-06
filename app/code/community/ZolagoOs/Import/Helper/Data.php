<?php

/**
 * Class ZolagoOs_Import_Helper_Data
 */
class ZolagoOs_Import_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_conf = array();

    public function getExternalId()
    {
        return $this->getConfig('external_id');
    }

    public function getProductFile()
    {
        return $this->getConfig('import_products');
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
}