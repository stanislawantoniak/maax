<?php
/**
 * soap tests
 */
class GH_Api_Model_SoapTest extends Zolago_TestCase {
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
            var_Dump($client->__getLastRequest());
            var_dump($client->__getLastResponse());
        }
        
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
            var_Dump($client->__getLastRequest());
            var_dump($client->__getLastResponse());
        }
    }
}