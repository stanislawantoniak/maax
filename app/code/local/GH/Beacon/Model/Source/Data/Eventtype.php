<?php

/**
 * Class Mage_Adminhtml_Model_System_Config_Source_Notification_Frequency
 */
class GH_Beacon_Model_Source_Data_Eventtype {

    /**
     * When customer come in range of beacon
     */
    const TYPE_INPUT  = 1;

    /**
     * When customer go out of beacon range
     */
    const TYPE_OUTPUT = 0;


    public function toOptionArray() {
        /** @var GH_Beacon_Helper_Data $helper */
        $helper = Mage::helper('ghbeacon');
        return array(
            0 => $helper->__('Output'),
            1 => $helper->__('Input'),
        );
    }
}
