<?php

abstract class Snowdog_Freshmail_Model_Api_Action_Abstract
    implements Snowdog_Freshmail_Model_Api_Action_Interface
{
    /**
     * Api client instance
     *
     * @var Snowdog_Freshmail_Model_Api_Client
     */
    protected $_api;

    /**
     * Retrieve api client instance
     *
     * @return Snowdog_Freshmail_Model_Api_Client
     */
    public function getApi()
    {
        if (null === $this->_api) {
            /** @var Snowdog_Freshmail_Model_Config $configModel */
            $configModel = Mage::getSingleton('snowfreshmail/config');
            $this->_api = Mage::getSingleton('snowfreshmail/api_client')
                ->setApiKey($configModel->getKey())
                ->setApiSecret($configModel->getSecret())
            ;
        }
        return $this->_api;
    }
}
