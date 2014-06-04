<?php
class Zolago_Catalog_Model_Observer
{

    static public function processConfigurableQueue()
    {
        Mage::log(microtime()."Starting processConfigurableQueue ", 0, 'configurable_update.log');
        Mage::getModel('zolagocatalog/queue_configurable')->process();
    }

    static public function clearConfigurableQueue()
    {
        Mage::getResourceModel('zolagocatalog/queue_configurable')->clearQueue();
    }
}