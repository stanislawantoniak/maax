<?php
/**
 * client poczta polska
 */
class Orba_Shipping_Model_Post_Client extends Orba_Shipping_Model_Client_Soap {


    protected $_default_params = array (
                                 );


    /**
     *
     */
    protected function _construct() {
        $this->_init('orbashipping/post_client');
    }
    
    
    /**
     * pack number
     */
     protected function _getGuid() {
         mt_srand((double)microtime()*10000);
         $charid = strtoupper(md5(uniqid(rand(), true)));
         $retval = substr($charid, 0, 32);
         return $retval;
     }
     
    /**
     * wsdl url 
     *
     * return string;
     */
    protected function _getWsdlUrl() {
        return Mage::getStoreConfig('carriers/zolagopp/gateway');
    }
    
    /**
     * @return array
     */
    protected function _getSoapMode() {
            $login = Mage::getStoreConfig('carriers/zolagopp/id');
            $password = Mage::getStoreConfig('carriers/zolagopp/password');
            $mode = array
                    (
                        'soap_version' => 'SOAP_1_1',  // use soap 1.1 client
                        'trace' => 1,
                        'login' => $login,
                        'password' => $password,
                    );
            return $mode;
    }
    
    /**
     * normalize postcode
     */
     protected function _normalizePostcode($code) {
         return str_replace('-','',$code);
     }
    /**
     * creating packs
     */
     public function createDeliveryPacks($settings) {
        if (!(int)$this->_settings['weight']) {
            Mage::throwException(Mage::helper('orbashipping')->__('No package weight'));
        }
        $sender = $this->_shipperAddress;        
        $message = Mage::getModel('orbashipping/post_message_pack_list')->getObject();
        $data = Mage::getModel('orbashipping/post_message_pack')->getObject();
        $address = Mage::getModel('orbashipping/post_message_address')->getObject();
        $address->nazwa = $sender['name'];
        $address->ulica = $sender['street'];
        $address->miejscowosc = $sender['city'];
        $address->kodPocztowy = $this->_normalizePostcode($sender['postcode']);
        $data->adres = $address;
        $data->iloscPotwierdzenOdbioru = 1;
        $data->kategoria = $this->_settings['category'];
        $data->gabaryt = $this->_settings['size'];
        $data->masa = $this->_settings['weight'];
        $data->guid = $this->_getGuid();
        $message->przesylki[] = $data;
        Mage::log($message);
        $result = $this->_sendMessage('addShipment',$message);
        Mage::log($result);
        Mage::throwException('die');
        return $this->_prepareResult($result);
     }
    /**
     * prepare inpost answer
     */
    protected function _prepareResult($data) {
		$xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $result = json_decode($json,true); // victorias trick
        return $result;
    }
    
    
    /**
     * labels to print
     */
    public function getLabels($tracking) {
        if (empty($tracking)) {
            return false;
        }
        if (!is_array($tracking)) {
            $tracking = array($tracking);
        }
        $codes = array();
        foreach ($tracking as $track) {
            $codes[] = $track->getNumber();
        }
        $message = Mage::getModel('orbashipping/packstation_inpost_message');
        $data = $message->getStickerMessage(
            $this->getAuth('username'),
            $this->getAuth('password'),
            $codes
        );        
        $out['data'] = $this->_sendMessage('getsticker', $data,'POST');
        $out['numbers'] = $codes;
        return $out;
    }

    
    /**
     * format results
     */
     public function processLabelsResult($method,$data) {
         try {
             $xml = simplexml_load_string($data['data']);
         } catch (Exception $x) {
             // if there is no xml - means ok
             $xml = false;
         }
         if ($xml === false) { // ok
             $result = array (
                 'status' => true,
                 'labelData' => $data['data'],
                 'labelName' => implode('_',$data['numbers']).'.'.Orba_Shipping_Helper_Packstation_Inpost::FILE_EXT,
                 'message' => 'Shipment ID: ' . implode(',',$data['numbers']),
             );
         } else {
             $tmp = $this->_prepareResult($data['data']);             
             if (!empty($tmp['error'])) {
                 $error = $tmp['error'];
             } else {
                 $error = 'INPOST Service error '.implode(',',$data['numbers']);
             }
             $result = array(
                 'status' => false,
                 'message' => $error
             );
         }
         return $result;
     }
     
    /**
     * cancel package
     */
     public function cancelPack($number) {
         $data = array (	
             'email' => $this->getAuth('username'),
             'password' => $this->getAuth('password'),
             'packcode' => $number             
         );
         $result = $this->_sendMessage('cancelpack',$data,'POST');
         if ($result !== '1') {
            $result = $this->_prepareResult($result);
         }
         return $result;
     }
     
    /**
     *  tracking
     */
     public function getPackStatus($number) {
         $data = array( 
             'packcode' => $number
         );
         $result = $this->_sendMessage('getpackstatus',$data);
		 
		 // uncomment for test perpouse; 
		 //$status = Orba_Shipping_Helper_Packstation_Inpost::INPOST_STATUS_DELIVERED;
		 //$result = "<paczkomaty><status>" . $status . "</status><statusDate>2016-04-09T15:57:03.692+02:00</statusDate></paczkomaty>";

         return $this->_prepareResult($result);
     }
}

