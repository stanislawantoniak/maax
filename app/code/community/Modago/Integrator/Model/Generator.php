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
    protected $_fileName;
    protected $_status;

	const DIRECTORY = 'modagointegrator';

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
        if (!$this->_fileName) {
            $this->_fileName = $this->getFileNamePrefix().'_'.$this->getExternalId().".xml";
        }
        return $this->_fileName;
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
        $this->_fileName = null;
        $helper = $this->getHelper();
        try {
            $helper->createFile($this->_getPath());
            $helper->addToFile($this->_getHeader());
            while ($list = $this->_prepareList()) {
                foreach ($list as $item) {
                    $block = $this->_prepareXmlBlock($item);
                    $helper->addToFile($block);
                }
            } 
            $helper->addToFile($this->_getFooter());
            $helper->closeFile();
            $this->_status = true;
        } catch (Modago_Integrator_Exception $ex) {
            Mage::logException($ex);
            $helper->closeFile();
        }
        return $this->_status;
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