<?php
/**
 * Zolago_Catalog_Helper_Pricetype
 *
 * @category    Zolago
 * @package     Zolago_Catalog
 *
 */
class Zolago_Catalog_Helper_Pricetype extends Mage_Core_Helper_Abstract
{

    protected $_priceTypeLogFile = 'converter_price_type.log';
    protected $_priceTypeQueueLogFile = 'converter_price_type_queue.log';

    /**
     * @param $ids
     */
    public static function queue($ids)
    {
        $model = Mage::getResourceModel('zolagocatalog/queue_pricetype');
        $model->addToQueue($ids);
    }


    /**
     * Add product to queue
     * @param $id
     */
    public static function queueProduct($id)
    {
        $model = Mage::getResourceModel('zolagocatalog/queue_pricetype');
        $model->addToQueueProduct($id);
    }


    /**
     * Special Log Message Function
     *
     * @param string $message Message to Log
     * @param string $logFile Log file name. Default: converter_price_type.log
     */
    public function _log($message, $logFile = false)
    {
        if (!$logFile) {
            $logFile = $this->_priceTypeLogFile;
        }

        Mage::log($message, null, $logFile, true);
    }

    /**
     * Special Log Message Function
     *
     * @param string $message Message to Log
     * @param string $logFile Log file name. Default: converter_price_type.log
     */
    public function _logQueue($message, $logFile = false)
    {
        if (!$logFile) {
            $logFile = $this->_priceTypeQueueLogFile;
        }

        Mage::log($message, null, $logFile, true);
    }

}