<?php

class Snowdog_Freshmail_Model_Resource_Api_Request extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init('snowfreshmail/api_request', 'request_id');
    }
}
