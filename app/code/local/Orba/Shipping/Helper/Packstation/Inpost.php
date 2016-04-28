<?php

/**
 * helper for inpost module
 */
class Orba_Shipping_Helper_Packstation_Inpost extends Orba_Shipping_Helper_Carrier {
    protected $_logFile = 'inpost_tracking.log';
    protected $_inpostClient;
    protected $_inpostLogin;
    protected $_inpostPassword;

    const FILE_DIR		= 'inpost';
    const FILE_EXT	= 'pdf';

    const INPOST_HEADER				= 'INPOST Tracking Info';
/*    const DHL_STATUS_DELIVERED	= 'DOR';
    const DHL_STATUS_RETURNED	= 'ZWN';
    const DHL_STATUS_WRONG		= 'AN';
    const DHL_STATUS_SHIPPED    = 'DWP';
    const DHL_STATUS_SORT       = 'SORT';
    const DHL_STATUS_LP         = 'LP';
    const DHL_STATUS_LK         = 'LK';
    const DHL_STATUS_AWI         = 'AWI';
    const DHL_STATUS_BGR         = 'BGR';
    const DHL_STATUS_OP          = 'OP';
    const DHL_CARRIER_CODE		= 'orbadhl';
    const ALERT_DHL_ZIP_ERROR = 1;
*/
  
    public function getHeader() {
        return self::INPOST_HEADER;
    }


    /**
     * Initialize INPOST Web API Client
     *
     * @param array $inpostSettings Array('login' => 'value', 'password' => 'value')
     *
     */
    public function startClient($inpostSettings = false)
    {
        if ($this->_inpostLogin === null || $this->_inpostPassword === null) {
            if ($inpostSettings) {
                $this->_inpostLogin	= $inpostSettings['login'];
                $this->_inpostPassword	= $inpostSettings['password'];
            }
            $inpostClient = Mage::getModel('orbashipping/packstation_client_inpost');
            $inpostClient->setAuth($this->_inpostLogin, $this->_inpostPassword);
            $this->_inpostClient = $inpostClient;
        }

        return $this->_inpostClient;
    }
}