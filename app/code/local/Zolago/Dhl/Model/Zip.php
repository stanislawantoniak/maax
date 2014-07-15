<?php

/**
 * Class Zolago_Dhl_Model_Zip
 *
 * @category    Zolago
 * @package     Zolago_Dhl
 *
 */
class Zolago_Dhl_Model_Zip extends Mage_Core_Model_Abstract
{

    /**
     * Init table
     */
    protected function _construct()
    {
        $this->_init('zolagodhl/zip');
        parent::_construct();
    }


}