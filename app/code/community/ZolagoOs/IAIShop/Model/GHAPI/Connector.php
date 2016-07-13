<?php

/**
 * Class ZolagoOs_IAIShop_Model_GHAPI_Connector
 */
class ZolagoOs_IAIShop_Model_GHAPI_Connector
    extends GH_Api_Model_Soap_Client
{

    protected $_vendor = false;

    private function getVendor()
    {
        if (!$this->_vendor) {
            $this->_vendor = Mage::getModel("zolagodropship/vendor")->load($vendorId);
        }
        return $this->_vendor;
    }

    private function getPassword()
    {
        if (!$this->_password) {
            $this->_password = $this->getVendor();
        }
        return $this->_password;
    }


    /**
     * GH API doLogin
     *
     * @param int $vendorId
     * @param string $password
     * @param string $apiKey
     * @return void
     */
    public function doLogin($vendorId)
    {
        $obj = new StdClass();
        $obj->vendorId = $vendorId;
        $obj->password = $password;
        $obj->webApiKey = trim($apiKey);
        return $this->_query('doLogin', $obj);
    }

    /**
     * GH API for getChangeOrderMessage
     *
     * @param string $token
     * @param int $batchSize
     * @param string $messageType
     * @param string $orderId
     * @return void
     */
    public function getChangeOrderMessage($token, $batchSize, $messageType, $orderId)
    {
        $obj = new StdClass();
        $obj->sessionToken = trim($token);
        $obj->messageBatchSize = $batchSize;
        $obj->messageType = $messageType;
        $obj->orderId = $orderId;
        return $this->_query('getChangeOrderMessage', $obj);
    }


    /**
     * soap query
     * @param string $name
     * @param stdClass $parameters
     * @return void
     */
    protected function _query($name, $parameters)
    {
        $testFlag = (bool)Mage::getConfig()->getNode('global/test_server');
        $params = array('encoding' => 'UTF-8', 'soap_version' => SOAP_1_2, 'trace' => 1);
        /** @var GH_Api_Helper_Data $helper */
        $helper = Mage::helper('ghapi');
        if ($testFlag) {
            $url = $helper->prepareWsdlUri($helper->getWsdlTestUrl(), $params);
        } else {
            $url = $helper->getWsdlTestUrl();
        }
        $client = new SoapClient($url, $params);
        $data = array();
        try {
            $data = $client->$name($parameters);

            if ($testFlag) {
                @unlink($url);
            }
            return $data;
        } catch (Exception $xt) {
            Mage::logException($xt);
        }
        return $data;
    }

}