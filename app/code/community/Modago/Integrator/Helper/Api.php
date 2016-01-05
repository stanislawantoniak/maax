<?php

/**
 * Class Modago_Integrator_Helper_Api
 */
class Modago_Integrator_Helper_Api extends Mage_Core_Helper_Abstract
{
    
    /**
     * get parameters from config by name
     * @param string $name
     * @return string
     */

    public function getConfig($name) {
        // dummy
        $out = array (
            'vendorId' => 5,
            'password' => 'testtest123',
            'apiKey' => 'cadeef539ae2ccca4e80ded78da5e48a0af3ec77d08489a5bde50232a65ff58d',
            'batchSize' => 3,
        );        
        return $out[$name];
        // todo
        
    }
}