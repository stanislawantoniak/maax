<?php

/**
 * helper for pp module
 */
class Orba_Shipping_Helper_Post extends Orba_Shipping_Helper_Carrier {
    protected $_logFile = 'pp_tracking.log';
    protected $_client;

    const FILE_DIR		= 'zolagopp';
    const FILE_EXT	= 'pdf';

    const PP_HEADER				= 'Poczta Polska Tracking Info';




    public function getHeader() {
        return self::PP_HEADER;
    }

    /**
     * Initialize PP Web API Client
     */
    public function startClient($settings = false)
    {
        if (!$this->_client) {
            $client = Mage::getModel('orbashipping/post_client');
            $this->_client = $client;
        }

        return $this->_client;
    }

    /**
     * inpost is active
     */
    public function isActive() {
        return Mage::getStoreConfig('carriers/zolagopp/active');
    }


}