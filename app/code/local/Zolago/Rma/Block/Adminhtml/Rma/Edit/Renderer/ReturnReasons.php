<?php

class Zolago_Rma_Block_Adminhtml_Rma_Edit_Renderer_ReturnReasons 
	extends Mage_Adminhtml_Block_Widget 
	implements Varien_Data_Form_Element_Renderer_Interface
{

    protected $_element = null;

    public function __construct()
    {
        $this->setTemplate('zolagorma/form/helper/returnreasons.phtml');
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
    	var_dump($element);
		exit();
        $this->setElement($element);
        return $this->toHtml();
    }

    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }

    public function getElement()
    {
        return $this->_element;
    }
	
	public function getReturnReasons(){
		
		return Mage::getModel("zolagorma/returnreason")->getCollection();
		
	}
    // public function getTopCategories()
    // {
        // return Mage::helper('udtiercom')->getTopCategories();
    // }
// 
    // public function getTiercomRates()
    // {
        // $value = $this->_element->getValue();
        // if (is_string($value)) {
            // $value = unserialize($value);
        // }
        // if (!is_array($value)) {
            // $value = array();
        // }
        // return $value;
    // }
// 
    // public function getGlobalTierComConfig()
    // {
        // $value = Mage::getStoreConfig('udropship/tiercom/rates');
        // if (is_string($value)) {
            // $value = unserialize($value);
        // }
        // return $value;
    // }
// 
    // public function getStore()
    // {
        // return Mage::app()->getDefaultStoreView();
    // }
}