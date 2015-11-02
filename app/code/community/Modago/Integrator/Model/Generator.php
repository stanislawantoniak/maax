<?php
/**
 * abstract object - generation file
 */
abstract class Modago_Integrator_Model_Generator
    extends Varien_Object {

	protected $_helper;
	protected $_externalId;

    /**
     * returns local path to generated file
     */
    abstract protected function _getPath();
    
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
        $status = false;
        $helper = $this->getHelper();
        try {
            $helper->createFile($this->_getPath());
            $helper->addToFile($this->_getHeader());
            while ($list = $this->prepareList()) {
                foreach ($list as $item) {
                    $block = $this->_prepareXmlBlock($item);
                    $helper->addToFile($block);
                }
            } 
            $helper->addToFile($this->_getFooter());
            $helper->closeFile();
            $status = true;
        } catch (Modago_Integrator_Exception $ex) {
            Mage::logException($ex);
            $helper->closeFile();
        }
        return $status;
    }
    
    
    /**
     * uploading file
     *
     */
     public function uploadFile() {
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