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
    protected $_compression;
    protected $_file;
    protected $_path;
    protected $_logFile;
    protected $_oldStore;

    public function getFileTypes() {
        return array(self::FILE_DESCRIPTIONS,self::FILE_PRICES,self::FILE_STOCKS);
    }


    /**
     * module version
     * @return string
     */
     public function getModuleVersion() {
         return Mage::getConfig()->getModuleConfig("Modago_Integrator")->version;
     }

    /**
     * path to log file
     *
     * @return string
     */
    public function getPathLogFile() {
        $dir = Modago_Integrator_Model_Generator::getDir();
        if (!is_dir($dir)) {
            mkdir($dir,0777,true);
        }
        $path = $dir.'integrator.log';
        return $path;
    }

    /**
     * compress using php funciton
     *
     * @param string $method
     * @param string $path
     * @return string|false
     *
     * @todo MEMORY LIMIT
     */

    protected function _compressByLib($method,$path) {
        $open = $method.'open';
        $close = $method.'close';
        $write = $method.'write';
        if (function_exists($open) && function_exists($close) && function_exists($write)) {
            @unlink($path.'.'.$method);
            $data = file_get_contents($path);
            if ($method == 'bz') {
                $method .= '2'; // extension bz2
            }
            $file = $open($path.'.'.$method,'w');
            if ($file) {
                $write($file,$data);
                $close($file);
                if (file_exists($path.'.'.$method)) {
                    $this->log('Compress parameters: '.$method,$path);
                    return '.'.$method;
                }
            }
        }
        return false;
    }
    /**
     * compress using shell methods
     *
     * @param string $method
     * @param string $path
     * @return string|false
     */
    protected function _compressTarFile($method,$path) {
        $function = sprintf('gzip %s',$path);
        if (function_exists($method)) {
            @unlink($path.'.gz');
            $method($function);
            if (file_exists($path.'.gz')) {
                $this->log('Compress parameters: '.$method,$path);
                return '.gz';
            }
        }
        return false;
    }
    /**
     * compress file and returned compressed file name
     *
     * @param string $path
     * @return string
     */

    public function compress($path) {
        // try exec
        if ($file = $this->_compressTarFile('exec',$path)) {
            return $file;
        }
        // try system
        if ($file = $this->_compressTarFile('system',$path)) {
            return $file;
        }
        // try zlib
        if ($file = $this->_compressByLib('gz',$path)) {
            return $file;
        }
        // try bz2
        if ($file = $this->_compressByLib('bz',$path)) {
            return $file;
        }
        return '';
    }

    protected function _prepareFile($path) {
        $folder = dirname($path);
        if(!is_dir($folder)) {
            try {
                mkdir($folder,0700,true);
            } catch(Exception $e) {
                Mage::logException($e);
	            $this->log('Could not create a folder '.$folder);
	            $this->throwException('Could not create a folder '.$folder);
            }
        }
        return fopen($path,'w');
    }

    public function createFile($path) {
        $this->_file = $this->_prepareFile($path);
        if($this->_file === false) {
	        $this->log('Cannot create file '.$path);
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
            $this->log('You have to create file first!');
            $this->throwException('You have to create file first!');
        }

        $written = fwrite($this->_file,$data);
        if($written === false) {
	        $this->log('Could not write to file '.$this->_path);
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
     * @param string|null $field
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

    public function getSecret()
    {
        return $this->getConfig('secret');
    }

    public function getExternalId()
    {
        return $this->getConfig('external_id');
    }

    public function getIntegrationStore()
    {
        if (!$storeId = $this->getConfig('integration_store')) {
            $storeId = Mage::app()->getDefaultStoreView()->getId();
        }
        return $storeId;    
    }

    /**
     * Send one file to Modago ftp server
     *
     * @param string $file path to file
     * @param string $fileName new/old file name with extension
     * @param string $ftpUrl url like: ftp://{$login}:{$passwd}@{$host}/{$filename}
     */
    public function sendToFtp($file, $fileName, $ftpUrl) {
        $ch = curl_init();
        $fp = fopen($file, 'r');
        curl_setopt($ch, CURLOPT_URL, $ftpUrl . $fileName);
        curl_setopt($ch, CURLOPT_UPLOAD, 1);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file));
        curl_exec($ch);
        $error_no = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error_no != 0) {
            Mage::logException(Mage::exception('Modago_Integrator', "Error occur while sending file ({$error})"));
            $this->log("Error occur while sending file ({$error})");
        } else {
            $this->log("Send by ftp finished");
        }
    }

    /**
     * log
     * @param string $message
     */
    public function log($message) {
        if (!$this->_logFile) {
            $path = $this->getPathLogFile();
            $this->_logFile = $path;
        }
        $line = sprintf('%s : [v. %s] %s',date('Y-m-d H:i:s'),$this->getModuleVersion(),$message);
        file_put_contents($this->_logFile,$line.PHP_EOL,FILE_APPEND);
    }

    /**
     * create generator model
     *
     * @param string $type
     * @return Modago_Integrator_Model_Generator|null
     */
    public function createGenerator($type) {
	    $model = null;
        switch($type) {
        case self::FILE_DESCRIPTIONS:
            $model = Mage::getModel('modagointegrator/generator_description');
            break;
        case self::FILE_PRICES:
            $model = Mage::getModel('modagointegrator/generator_price');
            break;
        case self::FILE_STOCKS:
            $model = Mage::getModel('modagointegrator/generator_stock');
            break;
        default:
			$this->log(sprintf('Wrong generate file type: %s',$type));
            $this->throwException(sprintf('Wrong generate file type: %s',$type));
        }
        return $model;

    }
    /**
     * function helps to read wsdl from self signed servers
     *
     * @param string $url wsdl file
     * @param array $params wsdl params
     * @return string
     */
    public function prepareWsdlUri($url,&$params) {
        $opts = array(
                    'ssl' => array('verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed' => true)
                );
        $params['stream_context'] = stream_context_create($opts);
        $file = file_get_contents($url,false,stream_context_create($opts));
        $dir = Mage::getBaseDir('var');
        $filename = $dir.'/'.uniqid().'.wsdl';        
        file_put_contents($filename,$file);        
        return $filename;
    }
    
    /**
     * check if flat catalog product is enabled
     *
     * @return bool
     */
    public function isFlat() {
        return Mage::helper('catalog/product_flat')->isEnabled();
    }
    
    /**
     * emulate admin store
     */

    public function saveOldStore() {
        if ($this->isFlat()) {
            $this->_oldStore = Mage::app()->getStore()->getId();
            Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID); // simulate admin
        }
    }
    
    /**
     * restore store id after emulation
     */

    public function restoreOldStore() {
        if ($this->isFlat()) {
            Mage::app()->getStore()->setId($this->_oldStore);        
        }
    }





}