<?php
/**
 * abstract rest carrier client
 */
class Orba_Shipping_Model_Client_Rest extends Orba_Shipping_Model_Client_Abstract {
        
	/**
	 * Send message to server
	 * 
	 * @param $method
	 * @param $data
	 * @param string $type
	 * @return array|mixed
	 * @throws Mage_Core_Exception
	 */
    protected function _sendMessage($get,$post,$type = 'GET') {
        if (!$service_url = $this->getParam('api')) {
            $service_url = $this->_getApiUrl();
        }
        try {
            $c = curl_init();
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            $url = $service_url.'?'.$this->_encodeParams($get);
            if ($type == 'GET') {
                curl_setopt($c, CURLOPT_HTTPGET, true);
            } else {
                curl_setopt($c,CURLOPT_POST,true);
                curl_setopt($c,CURLOPT_POSTFIELDS,$this->_encodeParams($post));
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
     * transform array to http
     */
    protected function _encodeParams($data) {
        $tmp = array();
        foreach ($data as $param=>$value) {
            $tmp[] = urlencode($param).'='.urlencode($value);
        }
        return implode('&',$tmp);
    }
    


}