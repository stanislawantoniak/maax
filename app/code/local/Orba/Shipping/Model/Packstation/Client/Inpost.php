<?php
/**
 * client inpost
 */
class Orba_Shipping_Model_Packstation_Client_Inpost extends Orba_Shipping_Model_Client_Abstract {

    const SHIPMENT_RMA_CONTENT      = 'Reklamacyjny zwrot do nadawcy';
    const AUTO_LABELS		= 0;
    const SELF_SEND			= 0;

    protected $_default_params = array (
                                     'autoLabels'	=> self::AUTO_LABELS,
                                     'selfSend'		=> self::SELF_SEND,
                                     'labelType'			=> '',
                                     'labelFormat'		=> '',
                                 );


    /**
     *
     */
    protected function _construct() {
        $this->_init('orbashipping/packstation_client_inpost');
    }
    
    /**
     * api url
     */


    protected function _getApiUrl() {
        return Mage::getStoreConfig('carriers/ghinpost/api');
    }

    /**
     * transform array to http
     */
    protected function _encodeParams($data) {
        $tmp = array();
        foreach ($data as $param=>$value) {
            $tmp[] = urlencode($param).'='.urlencode($value);
        }
        return implode('&',$tmp);
    }

    
    /**
     * create dispatch point
     */
    public function createDispatchPoint($pos) {
        $message = Mage::getModel('orbashipping/packstation_inpost_message');
        $data = $message->getCreateDispatchPointMessage(
             $this->getAuth('username'),
             $this->getAuth('password'),
             $pos
        ); 
        $result = $this->_sendMessage('createdispatchpoint',$data,'POST');
        return $this->_prepareResult($result);
    }
    /**
     * get dispatch point
     */
    public function getDispatchPoint($posName) {
        $message = Mage::getModel('orbashipping/packstation_inpost_message');
        $data = $message->getDispatchPointMessage(
            $this->getAuth('username'),
            $this->getAuth('password'),
            $posName);
       $result = $this->_sendMessage('getdispatchpoints',$data,'POST');
       return $this->_prepareResult($result);
    }

	/**
	 * Get retrieve array list with machines
	 * 
	 * @return array|mixed
	 */
	public function getListMachines() {
		$method = 'listmachines_xml';
		$return = $this->_sendMessage($method, array(), 'GET');		
		return $this->_prepareResult($return);
	}
	
	/**
	 * Send message to server
	 * 
	 * @param $method
	 * @param $data
	 * @param string $type
	 * @return array|mixed
	 * @throws Mage_Core_Exception
	 */
    protected function _sendMessage($method,$data,$type = 'GET') {
        if (!$url = $this->getParam('api')) {
            if (!$url = $this->_getApiUrl()) {
                Mage::throwException(Mage::helper('ghinpost')->__('Api Inpost not configured'));
            }
        }
        try {
            $c = curl_init();
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            if ($type == 'GET') {
                $data['do'] = $method;
                $url .= '?' . $this->_encodeParams($data);
                curl_setopt($c, CURLOPT_HTTPGET, true);
            } else {
                $url .= '?'.$this->_encodeParams(array('do'=>$method));
                $post = $this->_encodeParams($data);
                curl_setopt($c,CURLOPT_POST,true);
                curl_setopt($c,CURLOPT_POSTFIELDS,$post);
            }
            curl_setopt($c,CURLOPT_URL,$url);			
			$data = curl_exec($c);
			if (curl_errno($c) > 0) Mage::throwException(curl_error($c));
			$result = $data;
			curl_close($c);
        } catch (Exception $xt) {
            $result = $this->_prepareErrorMessage($xt);
        }
        return $result;
    }
    
    
    /**
     * creating packs
     */
     public function createDeliveryPacks($settings) {
         $message = Mage::getModel('orbashipping/packstation_inpost_message');
         $data = $message->getCreateDeliveryPackMessage(
            $this->getAuth('username'),
            $this->getAuth('password'),
            $settings                                  
            ); 
        $result = $this->_sendMessage('createdeliverypacks',$data,'POST');
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

