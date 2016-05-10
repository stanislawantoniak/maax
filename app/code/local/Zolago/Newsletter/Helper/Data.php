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
     * @param string $value
     * @param string $store
     * @return mixed Configuration setting
     */
    public function config($value, $store = null)
    {
        $store = is_null($store) ? Mage::app()->getStore() : $store;

        $configScope = Mage::app()->getRequest()->getParam('store');
        if ($configScope && ($configScope !== 'undefined') && !is_array($configScope)) {
            if (is_array($configScope) && isset($configScope['code'])) {
                $store = $configScope['code'];
            } else {
                $store = $configScope;
            }
        }

        return Mage::getStoreConfig("newsletter/zolagosubscription/$value", $store);
    }

}
