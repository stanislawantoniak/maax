<?php

/**
 * Class Zolago_Newsletter_Helper_Data
 */
class Zolago_Newsletter_Helper_Data extends Mage_Newsletter_Helper_Data
{

    /**
     * Check if Zolago_Newsletter module is enabled
     *
     * @return bool
     */
    public function isModuleActive()
    {
        return (bool)((int)$this->config('active') !== 0);
    }

    /**
     * Get module configuration value
     *
     * @param $value
     * @return mixed
     */
    public function config($value)
    {
        return Mage::getStoreConfig("newsletter/zolagosubscription/$value");
    }

}
