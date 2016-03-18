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
    
    /**
     * returns content
     */
    abstract public function prepareList();
    
    /**
     * @var array $item
     * preparing xml text block
     */
     abstract public function prepareXmlBlock($key,$item);
     
    /**
     * prepare header
     */
     abstract public function getHeader();
     
    /**
     * prepare footer
     */
     abstract public function getFooter();
             
    /**
     * set helper
     * @param $helper Modago_Integrator_Helper_Data
     */

     public function setHelper($helper) {
         $this->_helper = $helper;
     }
	/**
	 * @return Modago_Integrator_Helper_Data
	 */
     public function getHelper() {
	     if(!$this->_helper) {
		     $this->setHelper(Mage::helper("modagointegrator"));
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