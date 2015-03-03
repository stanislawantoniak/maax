<?php

/**
 * Class Orba_Shipping_Model_Zip
 *
 * @category    Orba
 * @package     Orba_Shipping
 *
 */
class Orba_Shipping_Model_Zip extends Mage_Core_Model_Abstract
{

    /**
     * Init table
     */
    protected function _construct()
    {
        $this->_init('orbashipping/zip');
        parent::_construct();
    }


}