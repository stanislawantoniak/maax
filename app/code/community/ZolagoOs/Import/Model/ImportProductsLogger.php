<?php

/**
 * Class ImportProductsLogger
 * Define a logger class that will receive all magmi logs *
 */
class ZolagoOs_Import_Model_ImportProductsLogger
{

    /**
     * logging methods
     *
     * @param string $data : log content
     * @param string $type : log type
     */
    public function log($data, $type)
    {
        Mage::log("$type:    $data", null, "zolagoosimport_magmi_log.log");
    }
}