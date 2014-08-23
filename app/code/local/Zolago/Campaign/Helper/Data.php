<?php
class Zolago_Campaign_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getBannerTypesList()
    {
        return Mage::getSingleton('zolagobanner/banner_type')->toOptionHash();
    }
}