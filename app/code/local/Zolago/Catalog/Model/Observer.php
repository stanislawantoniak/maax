<?php
/**
 * Class Zolago_Catalog_Model_Observer
 */
class Zolago_Catalog_Model_Observer
{

    static public function processConfigurableQueue()
    {
        Mage::log(microtime() . " Starting processConfigurableQueue ", 0, 'configurable_update.log');
        Mage::getResourceModel('zolagocatalog/queue_configurable')->clearQueue();
        Mage::getModel('zolagocatalog/queue_configurable')->process(2000);
    }

    static public function clearConfigurableQueue()
    {
        Mage::getResourceModel('zolagocatalog/queue_configurable')->clearQueue();
    }
}