<?php

/**
 * Class GH_Integrator_Model_Log
 * @method GH_Integrator_Model_Log setVendorId(int $vendorId)
 * @method int getVendorId()
 * @method GH_Integrator_Model_Log setLog(string $log)
 * @method string getLog()
 * @method string getCreatedAt()
 * @method int getId()
 */
class GH_Integrator_Model_Log extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('ghintegrator/log');
    }
}