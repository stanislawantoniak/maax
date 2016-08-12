<?php

/**
 * Class ZolagoOs_IAIShop_Model_GHAPI_Connector
 */
class ZolagoOs_IAIShop_Model_GHAPI_Connector
    extends GH_Api_Model_Soap_Client
{

    /**
     * GH API doLogin
     *
     * @param int $vendorId
     * @param string $password
     * @param string $apiKey
     * @return void
     */
    public function doLoginRequest($vendorId)
    {
        $vendor = Mage::getModel('udropship/vendor')->load($vendorId);
        if (!$vendor) {
            Mage::throwException('No vendor '.$vendorId);
        }        
        if (!$vendor->getData('ghapi_vendor_access_allow')) {
            return false;            
        }
        $ghApiUser = Mage::getModel('ghapi/user')->loadByVendorId($vendorId);
        if (!$ghApiUser) {
            return false;
        }
        $password = $ghApiUser->getPassword();
        $apiKey = $ghApiUser->getApiKey();
        $obj = new StdClass();
        $obj->vendorId = $vendorId;
        $obj->password = $password;
        $obj->webApiKey = $apiKey;
        return $this->_query('doLogin', $obj);
    }

    /**
     * GH API getOrdersByID
     * @param string $token
     * @param array $list
     * @return void
     */
    public function getOrdersByIDRequest($token, $list)
    {
        $obj = new StdClass();
        $obj->sessionToken = trim($token);
        $obj->orderID = $list;
        return $this->_query('getOrdersByID', $obj);
    }

	/**
	 * GH API getChangeOrderMessageRequest
	 * @param string $token
	 * @param int $batchSize
	 * @param string $messageType
	 * @param string $orderId
	 * @return array
	 */
	public function getChangeOrderMessageRequest($token, $batchSize, $messageType, $orderId = null) {
		$obj = new StdClass();
		$obj->sessionToken = trim($token);
		$obj->messageBatchSize = $batchSize;
		$obj->messageType = $messageType;
		$obj->orderId = $orderId;
		return $this->_query('getChangeOrderMessage',$obj);
	}

	/**
	 * GH API setOrderShipmentRequest
	 * @param string $token
	 * @param string $orderID
	 * @param string $dateShipped
	 * @param string $courier
	 * @param string $shipmentTrackingNumber
	 * @return void
	 */
	public function setOrderShipmentRequest($token, $orderID, $dateShipped, $courier, $shipmentTrackingNumber) {
		$obj = new StdClass();
		$obj->sessionToken = trim($token);
		$obj->orderID = $orderID;
		$obj->dateShipped = $dateShipped;
		$obj->courier = $courier;
		$obj->shipmentTrackingNumber = $shipmentTrackingNumber;
		$this->_query('setOrderShipment', $obj);
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
        $uri = Mage::getUrl('ghapi/wsdl/',array('_store'=>1));
        if ($testFlag) {            
            $url = $helper->prepareWsdlUri($uri, $params);
        } else {
            $url = $uri;
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