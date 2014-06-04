<?php
class Zolago_Catalog_Model_Observer
{

    static public function processConfigurableQueue()
    {
        Zolago_Catalog_Model_Observer::clearConfigurableQueue();
        Mage::getModel('zolagocatalog/queue_configurable')->process();
    }

    static public function clearConfigurableQueue()
    {
        Mage::getResourceModel('zolagocatalog/queue_configurable')->clearQueue();
    }
}