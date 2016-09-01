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


    /**
     * Retrieve not valid skus
     * Not valid if:
     * product is not connected to vendor
     * product don't exist
     *
     * @param $data
     * @param $vendorId
     * @return array
     */
    public function getNotValidSkus($data, $vendorId) {
        $inputSkus = array();
        foreach ($data as $sku => $item) {
            $inputSkus[$sku] = $sku;
        }

        /* @var Zolago_Catalog_Model_Resource_Product_Collection $coll */
        $coll = Mage::getResourceModel('zolagocatalog/product_collection');
        $coll->addFieldToFilter('sku', array( 'in' => $inputSkus));
        $coll->addAttributeToSelect('udropship_vendor', 'left');
        $coll->addAttributeToSelect('skuv', 'left');

        $_data = $coll->getData();


        $allSkusFromColl = array();
        $invalidOwnerSkus = array();

        // wrong owner
        foreach ($_data as $product) {
            $allSkusFromColl[$product['sku']] = $product['sku'];
            if ($product['udropship_vendor'] != $vendorId) {
                $invalidOwnerSkus[$product['sku']] = $product['sku'];
            }
        }

        // not existing products
        $notExistingSkus = array_diff($inputSkus, $allSkusFromColl);

        $allErrorsSkus = array_merge($invalidOwnerSkus, $notExistingSkus);

        // get skuv from sku
        foreach ($allErrorsSkus as $key => $sku) {
            $allErrorsSkus[$key] = $sku;
        }
        $allErrorsSkus = array_unique($allErrorsSkus);
        return $allErrorsSkus;
    }


    public function isContainNonNumericValues($data)
    {
        foreach ($data as $_) {
            if (!is_numeric($_)) {
                return true;
            }
        }
        return false;
    }

    public function isContainNegativeValues($data)
    {
        foreach ($data as $_) {
            if ((float)$_ < 0) {
                return true;
            }
        }
        return false;
    }
}