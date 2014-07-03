<?php

class Zolago_Converter_Model_Client{
	
	const URL_KEY = "{{key}}";
	
	protected $_conf = array();
	
	/**
	 * @return array ( 
	 *	'stock_url'	=> 'string', 
	 *	'login'		=> 'string', 
	 *	'password'	=> 'string'
	 * )
	 */
	public function getConfig($field=null){
		if(!$this->_conf){
			$this->_conf = Mage::getStoreConfig("zolagoconverter/config");
		}
		return $field ? $this->_conf[$field] : $this->_conf;
	}

	/**
	 * @param string $vendorExternalId
	 * @param string $posExternalId
	 * @param string $vendorSku
	 * @return int | null
	 */
	public function getQty($vendorExternalId, $posExternalId, $vendorSku) {
		$key = "\"" . $vendorExternalId . ":" . $vendorSku . "\"";
		$url = $this->_replaceUrlKey($this->getConfig('url_stock'), $key);
		$result=$this->_makeConnection($url);
				
		if(is_array($result) && isset($result['rows'])){
			foreach($result['rows'] as $row){
				if(isset($row['value']['pos']) && isset($row['value']['stock']) &&
					strtolower(trim($row['value']['pos']))==strtolower(trim($posExternalId))){
					return (int)$row['value']['stock'];
				}
			}
		}
		return null;
	}

    /**
     * @param $vendorExternalId
     * @param $priceType
     * @param $vendorSku
     *
     * @return null
     */
    public function getPrice($vendorExternalId, $vendorSku, $priceType)
    {
        $key = "\"" . $vendorExternalId . ":" . $vendorSku . "\"";
        $url = $this->_replaceUrlKey($this->getConfig('url_price'), $key);
        $result = $this->_makeConnection($url);
        
        if(isset($result['error'])){
            Mage::log(implode(' ,' , $result));
            return null;
        }
        if (is_array($result) && isset($result['rows'])) {
            foreach ($result['rows'] as $row) {
                if (isset($row['value']['price']) && !empty($row['value']['price'])) {
                    $prices = $row['value']['price'];
                    foreach ($prices as $priceConverterType => $pricesItem) {
                        if (strtolower($priceConverterType) == strtolower($priceType)) {
                            return $pricesItem;
                        }
                    }
                }
            }
        }
        return null;
    }
	
	/**
	 * @param string $url
	 * @param string $key
	 * @return string
	 */
	protected function _replaceUrlKey($url, $key) {
		return str_replace(self::URL_KEY, urlencode($key), $url);
	}
	
	
	/**
	 * @param type $url
	 * @return null | string
	 */
	protected function _makeConnection($url) {
		$return = null;
		try{
			$process = curl_init($url);
			curl_setopt($process, CURLOPT_HTTPHEADER, array(
				'Accept: application/json'
			));
			curl_setopt($process, CURLOPT_USERPWD, $this->getConfig('login') . ":" . $this->getConfig('password'));
			curl_setopt($process, CURLOPT_TIMEOUT, 30);
			curl_setopt($process, CURLOPT_HTTPGET, 1);
			curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
			$return = curl_exec($process);
			curl_close($process);
		}  catch (Exception $e){
			Mage::logException($e);
		}
			
		return Zend_Json::decode($return);
	}
	
}