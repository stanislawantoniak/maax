<?php

class ZolagoOs_IAIShop_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $_client;

    /**
     * @return ZolagoOs_IAIShop_Model_Client_Connector
     */
    public function getClient($vendorId)
    {
        if (!$this->_client) {
            $this->_client = Mage::getSingleton('zosiaishop/client_connector');
        }
        return $this->_client;
    }

    public function getProducts($params)
    {
        foreach ($params as $vendorId => $orders) {
            // Init IAI-Shop client for the vendor
            $client = $this->getClient($vendorId);
            $client->getProducts($vendorId, $orders);
        }
    }

    /**
     * @param $params
     */
    public function addOrders($params)
    {
        foreach ($params as $vendorId => $orders) {
            // Init IAI-Shop client for the vendor
            $client = $this->getClient($vendorId);
            $client->addOrders($vendorId, $orders);
        }
    }
}