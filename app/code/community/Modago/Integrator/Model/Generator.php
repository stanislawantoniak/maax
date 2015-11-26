<?php
/**
 * abstract object - generation file
 * @method string getFtpUrl()
 * @method Modago_Integrator_Model_Generator setFtpUrl(string $ftpUrl)
 */
abstract class Modago_Integrator_Model_Generator
    extends Varien_Object {

	protected $_helper;
	protected $_externalId;
    protected $_status;
    protected $_extension = '.xml';

	const DIRECTORY = 'modagointegrator';

	static public function getDir() {
	    return Mage::getBaseDir('var') . DS . self::DIRECTORY . DS ;
	}
    /**
     * Returns local path to generated file
     *
     * @return string
     */
    protected function _getPath() {
         return Mage::getBaseDir('var') . DS . self::DIRECTORY . DS . $this->_getFileName();
    }

    /**
     * File name for _getPath()
     *
     * @return string
     */
    protected function _getFileName() {
        return $this->getFileNamePrefix().'_'.$this->getExternalId().$this->_extension;
        
    }
    
    /**
     * returns content
     */
    abstract protected function _prepareList();
    
    /**
     * @var array $item
     * preparing xml text block
     */
     abstract protected function _prepareXmlBlock($item);
     
    /**
     * prepare header
     */
     abstract protected function _getHeader();
     
    /**
     * prepare footer
     */
     abstract protected function _getFooter();
    /**
     * generation file
     * 
     * @return bool
     */
    public function generate() {
        $this->_status = false;
        $helper = $this->getHelper();
        try {
            $helper->createFile($this->_getPath().'.tmp');
            $helper->log(sprintf('Create tmp file: %s.tmp',$this->_getPath()));
            $helper->addToFile($this->_getHeader());
            $helper->log('Save data begin');
            while ($list = $this->_prepareList()) {
                foreach ($list as $item) {
                    $block = $this->_prepareXmlBlock($item);
                    $helper->addToFile($block);
                }
            } 
            $helper->log('Save data end');
            $helper->addToFile($this->_getFooter());
            $helper->closeFile();            
            $helper->log('Close file');
            $this->_status = rename($this->_getPath().'.tmp',$this->_getPath());
            $helper->log(sprintf('Generate file: %s',($this->_status)? 'success':'fail'));            
        } catch (Modago_Integrator_Exception $ex) {
            Mage::logException($ex);
            $helper->log($ex->getMessage());
            $helper->closeFile();
        }
        return $this->_status;
    }
    
        
    /**
     * get data from generated files
     */

    public function getFile() {
        $path = $this->_getPath();
        $extensions = array(	
            '.gz',
            '.bz2',
            '',
        );
        foreach ($extensions as $ext) {
            $filename = trim($path.$ext);
            if (file_exists($filename)) {
                return $filename;
            }
        }
        return false;
    }
    
    /**
     * try compress file
     */
     public function compress() {
         $helper = $this->getHelper();
         $path = $this->_getPath();
         if ($extension = $helper->compress($path)) {
             @unlink($path);
         }
         $this->_extension .= $extension; // extension of compressed file
     }
    
    /**
     * Uploading file
     */
     public function uploadFile() {
         if ($this->_status) {
             $file = $this->_getPath();
             $fileName = $this->_getFileName();
             $this->getHelper()->sendToFtp($file, $fileName, $this->getFtpUrl());
         }
     }

	/**
	 * @return Modago_Integrator_Helper_Data
	 */
     public function getHelper() {
	     if(!$this->_helper) {
		     $this->_helper = Mage::helper("modagointegrator");
	     }
	     return $this->_helper;
     }

	public function getExternalId() {
		if(!$this->_externalId) {
			$this->_externalId = $this->getHelper()->getExternalId();
		}
		return $this->_externalId;
	}

}