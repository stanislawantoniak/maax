<?php
/**
 * events log
 */
class Modago_Integrator_Model_Log extends Mage_Core_Model_Abstract {
    
    /**
     * log event
     * @param string $text
     */
     static public function log($text) {
         // todo
         Mage::log($text);
     }
}