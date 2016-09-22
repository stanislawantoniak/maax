<?php

/**
 * Class ZolagoOs_OrdersExport_Helper_Data
 */
class ZolagoOs_OrdersExport_Helper_Data extends Mage_Core_Helper_Abstract
{


    /**
     * create log (file in var/log)
     *
     * @param $message
     */
    public function fileLog($message)
    {
        Mage::log($message, null, 'zosordersexport.log');
    }


}