<?php

class GH_Inpost_Helper_Data extends Mage_Core_Helper_Abstract {

    
    /**
     * api settings
     * @todo konfiguracja jest globalna na potrzeby Wójcika. Docelowo uzależnić od vendora/posa
     */
     public function getApiSettings($vendor,$pos) {
         $settings = array (
             'api' => Mage::getStoreConfig('carriers/ghinpost/api'),
             'login' => Mage::getStoreConfig('carriers/ghinpost/login'),
             'password' => Mage::getStoreConfig('carriers/ghinpost/password'),
         );
         return $settings;
     }

}