<?php
/**
 * soap tests
 */
class GH_Api_Model_SoapTest {
    protected $_client;
    protected function _getClient() {
        if (is_null($this->_client)) {
            $options = array( 
                'soap_version'=>SOAP_1_2, 
                'exceptions'=>true, 
                'trace'=>1, 
                'encoding'=>'UTF-8'
            );                           
            $url = 'http://modago.dev/ghapi/wsdl';
            $this->_client = new SoapClient($url,$options);
            
        }
        return $this->_client;
    }
    public function testLogin() {
        $client = $this->_getClient();
        $params = new StdClass();
        $params->vendorId = 1;
        $params->password = "passwoerd";
        $params->webApiKey = "api";
        var_dump($client->doLogin($params));
    }
	public function doLogin($vendorId,$password,$apiKey) {
		$client = $this->_getClient();
		$params = new StdClass();
		$params->vendorId = $vendorId;
		$params->password = $password;
		$params->webApiKey = $apiKey;
		var_dump($client->doLogin($params));
	}
    public function testGetChangeOrderMessage() {
        $client = $this->_getClient();
        $params = new StdClass();
        $params->sessionToken = 'token';
        $params->messageBatchSize = 6;
        $params->messageType = 'jakistype';    
        try {    
            var_dump($client->__getFunctions());
            var_dump($client->getChangeOrderMessage($params));
        } catch (Exception $xt) {
            var_dump($client->__getLastRequest());
            var_dump($client->__getLastResponse());
        }
        
    }
	public function getChangeOrderMessage($token,$batch,$type=null) {
		$client = $this->_getClient();
		$params = new StdClass();
		$params->sessionToken = $token;
		$params->messageBatchSize = $batch;
		$params->messageType = $type;
		var_dump($client->getChangeOrderMessage($params));
	}
    public function testSetChangeOrderMessageConfirmation() {
        $client = $this->_getClient();
        $obj = new StdClass();
        $list = array(1,2,3,4,5);
        $obj->messageIdList = $list;
        $obj->sessionToken = 'sessionToken';
        try {    
            var_dump($client->setChangeOrderMessageConfirmation($obj));
        } catch (Exception $xt) {
            var_dump($client->__getLastRequest());
            var_dump($client->__getLastResponse());
        }
    }
	public function setChangeOrderMessageConfirmation($token,$messages) {
		$client = $this->_getClient();
		$obj = new StdClass();
		$obj->messageIdList = $messages;
		$obj->sessionToken = $token;
		var_dump($client->setChangeOrderMessageConfirmation($obj));
	}
    public function getOrdersByID($token, $orderIds) {
        $client = $this->_getClient();
        $obj = new StdClass();
        $obj->sessionToken = $token;
        $obj->orderIds = $orderIds;
        var_dump($client->getOrdersByID($obj));
    }
    public function setOrderAsCollected($token, $orderIds) {
        $client = $this->_getClient();
        $obj = new StdClass();
        $obj->sessionToken = $token;
        $obj->orderIds = $orderIds;
        var_dump($client->setOrderAsCollected($obj));
    }
    public function setOrderShipment($token, $dateShipped, $courier, $shipmentTrackingNumber) {
        $client = $this->_getClient();
        $obj = new StdClass();
        $obj->sessionToken = $token;
        $obj->dateShipped  = $dateShipped;
        $obj->courier      = $courier;
        $obj->shipmentTrackingNumber = $shipmentTrackingNumber;
        var_dump($client->setOrderShipment($obj));
    }
}