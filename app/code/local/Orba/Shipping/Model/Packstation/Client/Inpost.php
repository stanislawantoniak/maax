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
        return $this->_sendMessage('createdispatchpoint',$data,'POST');
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
        return $this->_sendMessage('getdispatchpoints',$data,'POST');
    }

	/**
	 * Get retrieve array list with machines
	 * 
	 * @return array|mixed
	 */
	public function getListMachines() {
		$method = 'listmachines_xml';
		$return = $this->_sendMessage($method, array(), 'GET');
		return $return;
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
            Mage::throwException(Mage::helper('ghinpost')->__('Api Inpost not configured'));
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
                Mage::log($post);
            }
            curl_setopt($c,CURLOPT_URL,$url);
			
			$data = curl_exec($c);
			if (curl_errno($c) > 0) Mage::throwException(curl_error($c));
			$result = $this->_prepareResult($data);
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
        return $this->_sendMessage('createdeliverypacks',$data,'POST');
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
     * Process DHL Web API Shipments Result
     *
     * @param string $method
     * @param object $dhlResult
     *
     * @return array $result Default: array('shipmentId' => false, 'message' => '');
     */
    public function processDhlShipmentsResult($method, $dhlResult)
    {
        $result = array(
                      'shipmentId'	=> false,
                      'message'		=> ''
                  );
        $helper = Mage::helper('zolagopo');
        if (is_array($dhlResult) && array_key_exists('error', $dhlResult)) {
            //Dhl Error Scenario
            Mage::helper('orbashipping/carrier_dhl')->_log('DHL Service Error: ' .$dhlResult['error']);
            $result['shipmentId']	= false;
            $result['message']		= $helper->__('DHL Service Error: %s',$dhlResult['error']);
        }
        elseif (property_exists($dhlResult, 'createShipmentsResult') && property_exists($dhlResult->createShipmentsResult, 'item')) {
            $item = $dhlResult->createShipmentsResult->item;
            $result['shipmentId']	= $item->shipmentId;
            $result['message']		= $helper->__('Tracking ID: %s ', $item->shipmentId);
        }
        else {
            Mage::helper('orbashipping/carrier_dhl')->_log('DHL Service Error: ' .$method);
            $result['shipmentId']	= false;
            $result['message']		= $helper->__('DHL Service Error: %s', $method);
        }

        return $result;
    }


}
