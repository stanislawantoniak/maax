<?php

/**
 * Class ZolagoOs_Import_Model_Observer
 */
class ZolagoOs_Import_Model_Observer
{
    /**
     * Import products
     */
    public function cronImportProducts()
    {
        Mage::getModel("zolagoosimport/import_product")->runImport();
    }


    /**
     * Import prices, stock
     */
    public function cronImportPriceStock()
    {
        Mage::getModel("zolagoosimport/import_price")->runImport();
    }
}