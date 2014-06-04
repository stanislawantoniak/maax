<?php
class Zolago_Catalog_Model_Observer
{

    static public function processConfigurableQueue()
    {
        Mage::log(microtime()."Starting processConfigurableQueue ", 0, 'configurable_update.log');
        Zolago_Catalog_Model_Observer::clearConfigurableQueue();
        Mage::getModel('zolagocatalog/queue_configurable')->process(2000);
    }

    static public function clearConfigurableQueue()
    {
        Mage::getResourceModel('zolagocatalog/queue_configurable')->clearQueue();
    }
}