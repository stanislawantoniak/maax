<?php

class ZolagoOs_IAIShop_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $_client;

    /**
     * @return ZolagoOs_IAIShop_Client
     */
    public function getClient()
    {
        if (!$this->_client) {
            $this->_client = Mage::getSingleton('zosiaishop/client');
        }
        return $this->_client;
    }

    public function getProducts($request)
    {
        $client = $this->getClient();
        return $client->getProducts($request);
    }
}