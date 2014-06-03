<?php
class Zolago_Catalog_Helper_Configurable extends Mage_Core_Helper_Abstract
{
    public static function queue($ids)
    {
        $model = Mage::getResourceModel('zolagocatalog/queue_configurable');
        $model->addToQueue($ids);
    }

}