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
     * @return void
     */
     public function getChangeOrderMessage($token,$batchSize,$messageType) {
         $obj = new StdClass();
         $obj->sessionToken = trim($token);
         $obj->messageBatchSize = $batchSize;
         $obj->messageType = $messageType;
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
        $client = new SoapClient(Mage::helper('ghapi')->getWsdlTestUrl(),array('trace'=>true));
        try {
        
            $client->$name($parameters);
        } catch (Exception $xt) {
            Mage::logException($xt);
        }
        if ($this->block) {
            $this->block->setSoapRequest($this->_format($client->__getLastRequest()));
            $this->block->setSoapResponse($this->_format($client->__getLastResponse()));
        }
    }

}