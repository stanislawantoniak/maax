<?php
class Zolago_Catalog_Model_Observer {


    static public function processConfigurableQueue()
    {

        Mage::getModel('zolagocatalog/queue_configurable')->process();
    }
}