<?php

class Zolago_Rma_Block_Adminhtml_Rma_Edit_Renderer_ReturnReasons 
	extends Mage_Adminhtml_Block_Widget 
	implements Varien_Data_Form_Element_Renderer_Interface
{

    protected $_element = null;
	protected $_mode;
	
    public function __construct()
    {
        $this->setTemplate('zolagorma/form/helper/returnreasons.phtml');
    }
	
	/**
	 * @param Varien_Data_Form_Element_Abstract $elem
	 * 
	 * @return html
	 */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
	
	/**
	 * @param Varien_Data_Form_Element_Abstract $elem
	 * 
	 * @return Zolago_Rma_Block_Adminhtml_Rma_Edit_Renderer_ReturnReasons
	 */
    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }
	
	/**
	 * @return Zolago_Rma_Block_Adminhtml_Rma_Edit_Renderer_ReturnReasons
	 */
    public function getElement()
    {
        return $this->_element;
    }
	
	/**
	 * @return Zolago_Rma_Model_Resource_VendorReturnReason_Collection | NULL
	 */
	public function getCollection(){
		
		$collection = NULL;
		$vendor = $this->getVendor();
		// Is Edit mode
		if($vendor){
			$this->_mode = 'edit';				
			$collection = $vendor->getRmaReasonVendorCollection();
		}
		
		if(!$collection || $collection->count() == 0){
			$this->_mode = 'new';
			$collection = Mage::getModel("zolagorma/rma_reason")->getCollection();
		}
		
		return $collection;
	}
	
	/**
	 * @return string
	 */
	public function getMode(){
		return $this->_mode;
	}
	
	/**
	 * @return Udropship_Model_Vendor
	 */
	public function getVendor(){
		$params = Mage::app()->getFrontController()->getRequest()->getParams();
		$vendor_id = $params['id'];
		
		if($vendor_id){
			$vendor = Mage::getModel('udropship/vendor')->load($params['id']);
			return ($vendor) ? $vendor : NULL;
		}
		else{
			return NULL;
		}
	}
    
}