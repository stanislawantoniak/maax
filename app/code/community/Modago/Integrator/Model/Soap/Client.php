<?php
/**
 * client class used for testing soap requests
 */
class Modago_Integrator_Model_Soap_Client  {

    const MODAGO_API_WSDL = 'https://modago.dev/ghapi/wsdl/';
    /**
     * login
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
        return $this->_query('doLogin',$obj);
    }
    
    /**
     * Test for getChangeOrderMessage
     *
     * @param string $token
     * @param int $batchSize
     * @param string $messageType
     * @return void
     */
     public function getChangeOrderMessage($token,$batchSize,$messageType) {
         $obj = new StdClass();
         $obj->sessionToken = trim($token);
         $obj->messageBatchSize = $batchSize;
         $obj->messageType = $messageType;
         return $this->_query('getChangeOrderMessage',$obj);
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
         return $this->_query('setChangeOrderMessageConfirmation',$obj);
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
         return $this->_query('getOrdersByID',$obj);
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
     * @return void
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
     * @return void
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
     * @return void
     */
    protected function _query($name,$parameters) {
        $params = array ('encoding' => 'UTF-8', 'soap_version' => SOAP_1_1, 'trace' => 1,'cache_wsdl'=>WSDL_CACHE_NONE);
        try {
            $url = Mage::helper('modagointegrator')->prepareWsdlUri(self::MODAGO_API_WSDL,$params);
//            $url = self::MODAGO_API_WSDL;
            $client = new SoapClient($url,$params);
            $data = array();
            $data = $client->$name($parameters);
        } catch (Exception $xt) {
            Mage::logException($xt);
            Modago_Integrator_Model_Log::log($xt->getMessage());
        }
        unlink($url);
        return $data;
    }
}