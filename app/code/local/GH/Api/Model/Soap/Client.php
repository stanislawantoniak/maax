<?php
/**
 * client class used for testing soap requests
 */
class GH_Api_Model_Soap_Client  {

    protected $block;

    /**
     * Set answer renderer
     *
     * @param GH_Api_Block_Dropship_Answer $block
     * @return void
     */
    public function setBlock($block) {
        $this->block = $block;
    }

    /**
     * Test for doLogin
     *
     * @param int $vendorId
     * @param string $password
     * @param string $apiKey
     * @return void
     */
    public function doLogin($vendorId,$password,$apiKey) {
        $obj = new StdClass();
        $obj->vendorId = $vendorId;
        $obj->password = $password;
        $obj->webApiKey = trim($apiKey);
        $this->_query('doLogin',$obj);
    }
    
    /**
     * Test for getChangeOrderMessage
     *
     * @param string $token
     * @param int $batchSize
     * @param string $messageType
     * @param string $orderId
     * @return void
     */
     public function getChangeOrderMessage($token,$batchSize,$messageType,$orderId) {
         $obj = new StdClass();
         $obj->sessionToken = trim($token);
         $obj->messageBatchSize = $batchSize;
         $obj->messageType = $messageType;
         $obj->orderId = $orderId;
         $this->_query('getChangeOrderMessage',$obj);
     }

    /**
     * Test for setChangeOrderMessageConfirmation
     * @param string $token
     * @param array $list
     * @return void
     */
     public function setChangeOrderMessageConfirmation($token,$list) {
         $obj = new StdClass();
         $obj->sessionToken = trim($token);
         $obj->messageID = $list;
         $this->_query('setChangeOrderMessageConfirmation',$obj);
     }

    /**
     * Test for getOrdersByID
     * @param string $token
     * @param array $list
     * @return void
     */
     public function getOrdersByID($token,$list) {
         $obj = new StdClass();
         $obj->sessionToken = trim($token);
         $obj->orderID = $list;
         $this->_query('getOrdersByID',$obj);
     }

    /**
     * Test for setOrderAsCollected
     * @param string $token
     * @param array $list
     * @return void
     */
    public function setOrderAsCollected($token, $list) {
        $obj = new StdClass();
        $obj->sessionToken = trim($token);
        $obj->orderID = $list;
        $this->_query('setOrderAsCollected', $obj);
    }

    /**
     * Test for setOrderShipment
     *
     * @param string $token
     * @param string $orderID
     * @param string $dateShipped
     * @param string $courier
     * @param string $shipmentTrackingNumber
     * @return void
     */
    public function setOrderShipment($token, $orderID, $dateShipped, $courier, $shipmentTrackingNumber) {
        $obj = new StdClass();
        $obj->sessionToken = trim($token);
        $obj->orderID = $orderID;
        $obj->dateShipped = $dateShipped;
        $obj->courier = $courier;
        $obj->shipmentTrackingNumber = $shipmentTrackingNumber;
        $this->_query('setOrderShipment', $obj);
    }

    /**
     * Test for setOrderReservation
     *
     * @param string $token
     * @param string $orderID
     * @param string $reservationStatus
     * @param string $reservationMessage
     * @return void
     */
    public function setOrderReservation($token, $orderID, $reservationStatus, $reservationMessage) {
        $obj = new StdClass();
        $obj->sessionToken = trim($token);
        $obj->orderID = $orderID;
        $obj->reservationStatus = $reservationStatus;
        $obj->reservationMessage = $reservationMessage;
        $this->_query('setOrderReservation', $obj);
    }

    /**
     * Test for getCategories
     * @param $token
     */
    public function getCategories($token) {
        $obj = new StdClass();
        $obj->sessionToken = trim($token);
        $this->_query('getCategories', $obj);
    }

	/**
	 * test for getUpdateProductsPricesStocks
	 *
	 * @param $token
	 * @param $data
	 */
	public function updateProductsPricesStocks($token, $data) {
		$obj = new StdClass();
		$obj->sessionToken = trim($token);
		$obj->productsPricesUpdateList = $data['productsPricesUpdateList'];
        $obj->productsStocksUpdateList = $data['productsStocksUpdateList'];
		$this->_query('updateProductsPricesStocks', $obj);
		/** @see GH_Api_Model_Soap::updateProductsPricesStocks() */
	}

    /**
     * xml formatter
     * @param string $xml
     * @return string
     */
    protected function _format($xml) {
        try {
            $doc = new DomDocument();
            $doc->loadXML($xml);
            $doc->formatOutput = true;
            $doc->preserveWhiteSpace = false;
            return htmlentities($doc->saveXML());
        } catch (Exception $xt) {
            return Mage::helper('ghapi')->__('Not xml document (%s)',$xml);
        }

    }

    /**
     * soap query
     * @param string $name
     * @param stdClass $parameters
     * @return void
     */
    protected function _query($name,$parameters) {
        $testFlag = (bool)Mage::getConfig()->getNode('global/test_server');
        $params = array ('encoding' => 'UTF-8', 'soap_version' => SOAP_1_2, 'trace' => 1);
		/** @var GH_Api_Helper_Data $helper */
		$helper = Mage::helper('ghapi');
        if ($testFlag) {
            $url = $helper->prepareWsdlUri($helper->getWsdlTestUrl(), $params);
        } else {
            $url = $helper->getWsdlTestUrl();
        }
        $client = new SoapClient($url,$params);
        $data = array();
        try {
            $data = $client->$name($parameters);
            Mage::log($data, null, "12345.log");
            if ($testFlag) {
                 @unlink($url);   
            }          
        } catch (Exception $xt) {
            Mage::logException($xt);
        }
        if ($this->block) {
            $this->block->setSoapRequest($this->_format($client->__getLastRequest()));
            $this->block->setSoapResponse($this->_format($client->__getLastResponse()));
            if (isset($data->token)) {
                $this->block->setToken($data->token);
            }
        }
    }

}