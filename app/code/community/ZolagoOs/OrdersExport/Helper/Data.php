<?php

/**
 * Class ZolagoOs_OrdersExport_Helper_Data
 */
class ZolagoOs_OrdersExport_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $_conf = array();
    
    public function getExternalId()
    {
        return $this->getConfig('external_id');
    }

    /**
     * @param null $field
     * @return array|mixed|string
     */
    public function getConfig($field = null)
    {
        if (!$this->_conf) {
            $this->_conf = Mage::getStoreConfig("zosordersexport/general");
        }
        return $field ? trim($this->_conf[$field]) : $this->_conf;
    }


    /**
     * @return array|mixed|string
     */
    public function getExportDirectory()
    {
        return $this->getConfig('export_files_folder');
    }



    /**
     * @param $value
     * @return string
     */
    public function formatToDocNumber($value)
    {
        //Format liczb    : bez separatora tysiêcy, separator dziesiêtny "."
        $decPoint = '.';
        $thousandsSep = '';
        return number_format($value, 2, $decPoint, $thousandsSep);
    }


    /**
     * @param $text
     * @return string
     */
    public function toWindows1250($text)
    {
        $text = trim($text);
        return trim(iconv('UTF-8', "Windows-1250", $text));
    }

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