<?php

/**
 * Model for data from beacons - offline history of customer
 *
 * Class GH_Beacon_Model_Data
 *
 * @method string getId()
 * @method string getBeaconId()
 * @method string getEmail()
 * @method string getDistance()
 * @method string getDate()
 * @method string getEventType()
 */
class GH_Beacon_Model_Data extends Mage_Core_Model_Abstract {

    protected function _construct() {
        $this->_init('ghbeacon/data');
        parent::_construct();
    }
}

