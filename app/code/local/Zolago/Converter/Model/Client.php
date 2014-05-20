<?php

class Zolago_Converter_Model_Client{
	
	protected $_conf = array();
	/**
	 * @var SoapClient
	 */
	protected $_client;
	
	/**
	 * @return SoapClient
	 */
	public function getClient() {
		 if(!$this->_client){
			$config = $this->getConfig();
			$options = array(
				"location" => $config['location'],
				"uri" => $config['uri']
			);
			$this->_client = new SoapClient(null, $options);
		 }
		 return $this->_client;
	}
	
	/**
	 * @return array ( 
	 *	'location'	=> 'string', 
	 *	'uri'		=> 'string', 
	 *	'apikey'	=> 'string', 
	 *	'login'		=> 'string', 
	 *	'password'	=> 'string'
	 * )
	 */
	public function getConfig(){
		if(!$this->_conf){
			$this->_conf = Mage::getStoreConfig("zolagoconverter/config");
		}
		return $this->_conf;
	}
	
	/**
	 * @todo implement
	 * @param string $vsku
	 * @param string $posExternalId
	 * @return int
	 */
	public function getQtyForPos($vsku, $posExternalId) {
		return 0;
	}
	
}