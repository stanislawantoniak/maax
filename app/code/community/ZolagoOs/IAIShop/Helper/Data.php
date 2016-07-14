<?php

class ZolagoOs_IAIShop_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $_client;

    /**
     * @return ZolagoOs_IAIShop_Model_Client_Connector
     */
    public function getIAIShopConnector($vendorId)
    {
        if (!$this->_client) {
            $connector = Mage::getSingleton('zosiaishop/client_connector');
            $connector->setVendorId($vendorId);
            $this->_client = $connector;
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
            $iaiShopConnector = $this->getIAIShopConnector($vendorId);
            $iaiShopConnector->addOrders($orders);
        }
    }
}