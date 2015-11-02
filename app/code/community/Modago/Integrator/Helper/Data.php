<?php

/**
 * Class Modago_Integrator_Helper_Data
 */
class Modago_Integrator_Helper_Data extends Mage_Core_Helper_Abstract
{
	const STATUS_ERROR = 'ERROR'; //Modago_Integrator error - error that we've been expecting
	const STATUS_FATAL_ERROR = 'FATAL'; //Modago server error, contact Modago administration
	const STATUS_OK = 'OK';
	const FILE_DESCRIPTIONS = 'DESCRIPTIONS';
	const FILE_PRICES = 'PRICES';
	const FILE_STOCKS = 'STOCKS';

	protected $_conf = array();

	protected $_file;
	protected $_path;

	public function getFileTypes() {
		return array(self::FILE_DESCRIPTIONS,self::FILE_PRICES,self::FILE_STOCKS);
	}


	public function createFile($path) {
		$folder = dirname($path);
		if(!is_dir($folder)) {
			try {
				mkdir($folder,0600,true);
			} catch(Exception $e) {
				Mage::logException($e);
				$this->throwException('Could not create a folder '.$folder);
			}
		}

		$this->_file = fopen($path,'w');
		if($this->_file === false) {
			$this->throwException('Cannot create file '.$path);
			$this->_file = null;
		} else {
			$this->_path = $path;
			$this->addToFile('<?xml version="1.0" encoding="UTF-8"?>');
		}
		return $this;
	}

	public function addToFile($data) {
		if(is_null($this->_file) || is_null($this->_path)) {
			$this->throwException('You have to create file first!');
		}

		$written = fwrite($this->_file,$data);
		if($written === false) {
			$this->throwException('Could not write to file '.$this->_path);
		}
		return $this;
	}

	public function closeFile() {
		fclose($this->_file);
		$this->_file = null;
		return $this;
	}

	public function throwException($msg,$code=0) {
		throw Mage::exception("Modago_Integrator",$msg,$code);
	}

	/**
	 * @return array (
	 *    'secret'        => 'string',
	 *    'external_id'    => 'string'
	 * )
	 */
	public function getConfig($field = null)
	{
		if (!$this->_conf) {
			$this->_conf = Mage::getStoreConfig("modagointegrator/authentication");
		}
		return $field ? trim($this->_conf[$field]) : $this->_conf;
	}

	public function getSecret() {
		return $this->getConfig('secret');
	}

	public function getExternalId() {
		return $this->getConfig('external_id');
	}
}