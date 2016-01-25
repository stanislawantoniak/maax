<?php
/**
 * client class used for testing soap requests
 */
class Modago_Integrator_Model_Soap_Client  {

    const MODAGO_API_WSDL = 'https://modago.pl/ghapi/wsdl/';

    /**
     * login
     *
     * @param int $vendorId
     * @param string $password
     * @param string $apiKey
     * @return array
     */
    public function doLogin($vendorId,$password,$apiKey) {
        $obj = new StdClass();
        $obj->vendorId = $vendorId;
        $obj->password = $password;
        $obj->webApiKey = trim($apiKey);
        return $this->_query('doLogin',$obj);
    }
    
    /**
     * Test for getChangeOrderMessage
     *
     * @param string $token
     * @param int $batchSize
     * @param string $messageType
     * @param string $orderId
     * @return array
     */
     public function getChangeOrderMessage($token,$batchSize,$messageType,$orderId = null) {
         $obj = new StdClass();
         $obj->sessionToken = trim($token);
         $obj->messageBatchSize = $batchSize;
         $obj->messageType = $messageType;
         $obj->orderId = $orderId;
         return $this->_query('getChangeOrderMessage',$obj);
     }

    /**
     * Test for setChangeOrderMessageConfirmation
     * @param string $token
     * @param array $list
     * @return array
     */
     public function setChangeOrderMessageConfirmation($token,$list) {
         $obj = new StdClass();
         $obj->sessionToken = trim($token);
         $obj->messageID = $list;
         return $this->_query('setChangeOrderMessageConfirmation',$obj);
     }

    /**
     * Test for getOrdersByID
     * @param string $token
     * @param array $list
     * @return array
     */
     public function getOrdersByID($token,$list) {
         $obj = new StdClass();
         $obj->sessionToken = trim($token);
         $obj->orderID = $list;
         return $this->_query('getOrdersByID',$obj);
     }

    /**
     * Test for setOrderAsCollected
     * @param string $token
     * @param array $list
     * @return array
     */
    public function setOrderAsCollected($token, $list) {
        $obj = new StdClass();
        $obj->sessionToken = trim($token);
        $obj->orderID = $list;
        return $this->_query('setOrderAsCollected', $obj);
    }

    /**
     * Test for setOrderShipment
     *
     * @param string $token
     * @param string $orderID
     * @param string $dateShipped
     * @param string $courier
     * @param string $shipmentTrackingNumber
     * @return array
     */
    public function setOrderShipment($token, $orderID, $dateShipped, $courier, $shipmentTrackingNumber) {
        $obj = new StdClass();
        $obj->sessionToken = trim($token);
        $obj->orderID = $orderID;
        $obj->dateShipped = $dateShipped;
        $obj->courier = $courier;
        $obj->shipmentTrackingNumber = $shipmentTrackingNumber;
        return $this->_query('setOrderShipment', $obj);
    }

    /**
     * Test for setOrderReservation
     *
     * @param string $token
     * @param string $orderID
     * @param string $reservationStatus
     * @param string $reservationMessage
     * @return array
     */
    public function setOrderReservation($token, $orderID, $reservationStatus, $reservationMessage) {
        $obj = new StdClass();
        $obj->sessionToken = trim($token);
        $obj->orderID = $orderID;
        $obj->reservationStatus = $reservationStatus;
        $obj->reservationMessage = $reservationMessage;
        return $this->_query('setOrderReservation', $obj);
    }

    /**
     * Test for getCategories
     * @param $token
     * @return array
     */
    public function getCategories($token) {
        $obj = new StdClass();
        $obj->sessionToken = trim($token);
        return $this->_query('getCategories', $obj);
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
     * @return array
     */
    protected function _query($name,$parameters) {
        $params = array ('encoding' => 'UTF-8', 'soap_version' => SOAP_1_1, 'trace' => 1,'cache_wsdl'=>WSDL_CACHE_NONE,'features'=> SOAP_SINGLE_ELEMENT_ARRAYS);
        $data = array();
        /** @var Modago_Integrator_Helper_Data $helper */
        $helper = Mage::helper('modagointegrator');
        /** @var Modago_Integrator_Helper_Api $helperApi */
        $helperApi = Mage::helper('modagointegrator/api');
        try {
            $url = $helper->prepareWsdlUri($helperApi->getApiUrl(), $params);
            $client = new SoapClient($url,$params);

            $data = $client->$name($parameters);
        } catch (Exception $xt) {
            Mage::logException($xt);
			Mage::helper('modagointegrator/api')->log($xt->getMessage());
        }
        if (!empty($url)) {
            unlink($url);
        }
        return $data;
    }
}