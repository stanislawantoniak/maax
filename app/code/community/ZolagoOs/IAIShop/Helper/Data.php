<?php

class ZolagoOs_IAIShop_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $_client;

    /**
     * @return GH_Wfirma_Model_Client
     */
    public function getClient()
    {
        if (!$this->_client) {
            $this->_client = Mage::getSingleton('zolagoosiaishop/client');
        }
        return $this->_client;
    }

    public function getProducts($request)
    {
        $client = $this->getClient();
        return $client->getProducts($request);
    }
}