<?php

class Orba_Shipping_Model_Packstation_Client_Pwr extends Orba_Shipping_Model_Client_Soap {

	protected function _construct() {
		$this->_init('orbashipping/packstation_client_pwr');
	}

	/**
	 * @param $method
	 * @param null $message
	 * @return array
	 */
	protected function _sendMessage($method, $message = null) {
		try {
			$wsdl = $this->_getWsdlUrl();
			$mode = $this->_getSoapMode();
			$soap = new SoapClient($wsdl, $mode);
			$header = $this->_prepareSoapHeader();
			if ($header) {
				$soap->__setSoapHeaders($header);
			}
			$result = $soap->$method($message);
			//Mage::log($soap->__getLastRequest());
			//Mage::log($soap->__getLastResponse());
		} catch (Exception $xt) {
			//Mage::logException($xt);
			$result = $this->_prepareErrorMessage($xt);
		}
		return $result;
	}


	/**
	 * WSDL url
	 *
	 * @return string
	 */
	protected function _getWsdlUrl() {
		return $this->getHelper()->getApiWsdl();
	}

	/**
	 * @return array
	 */
	protected function _getSoapMode() {
		$mode = array (
			'soap_version' => SOAP_1_1,  // use soap 1.1 client
			'trace' => 1,
		);
		// for tests disable 
		$testFlag = (bool)Mage::getConfig()->getNode('global/test_server');
		if ($testFlag) {
			// for tests servers skip ssl security 
			$mode['stream_context'] = stream_context_create(
				array(
					'ssl' => array(
						// set some SSL/TLS specific options
						'verify_peer' => false,
						'verify_peer_name' => false,
						'allow_self_signed' => true
					)
				)
			);
		}
		return $mode;
	}
	
	/**
	 * @return ZolagoOs_Pwr_Helper_Data
	 */
	public function getHelper() {
		/** @var ZolagoOs_Pwr_Helper_Data $helper */
		$helper = Mage::helper("zospwr");
		return $helper;
	}

	/**
	 * @return mixed|array
	 */
	public function giveMeAllRUCHLocation() {
		$message = new StdClass();
		$message->PartnerID = $this->getHelper()->getPartnerId();
		$message->PartnerKey = $this->getHelper()->getPartnerKey();
		$data = $this->_sendMessage("GiveMeAllRUCHLocation", $message);
		$result = $this->_prepareResult($data);
		return $result['NewDataSet']['AllRUCHLocation'];
	}

	/**
	 * Prepare answer
	 * 
	 * @param $data
	 * @return mixed|array
	 */
	protected function _prepareResult($data) {
		$xml = simplexml_load_string($data->GiveMeAllRUCHLocationResult->any, "SimpleXMLElement", LIBXML_NOCDATA);
		$json = json_encode($xml);
		$result = json_decode($json,true);
		return $result;
	}
}

