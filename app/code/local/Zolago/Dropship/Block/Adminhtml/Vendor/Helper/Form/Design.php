<?php


class Zolago_Dropship_Block_Adminhtml_Vendor_Helper_Form_Design 
    extends Varien_Data_Form_Element_Abstract{

	
	/**
	 * @return Mage_Core_Model_Resource_Store_Collection
	 */
	public function getStoreCollection() {
		return Mage::getResourceModel('core/store_collection');
	}
	
	public function isMultiWebsites() {
		return !Mage::app()->isSingleStoreMode();
	}
	
	public function getForm() {
		if(!$this->_form){
			$this->_form = new Varien_Data_Form(); 
		}
		return $this->_form;
	}
	
	public function getDesignSelect($id, $value=null) {
		$select = new Varien_Data_Form_Element_Select(array(
            'title'    => Mage::helper('core')->__('Custom Design'),
            'values'   => Mage::getSingleton('core/design_source_design')->getAllOptions(),
            'name'     => 'custom_design['.$id.']',
			'value'	   => $value
        ));
		$select->setId("custom_design_$id");
		$this->getForm()->addElement($select);
		return $select->toHtml();
	}
	
	public function getElementHtml() {
		$block = Mage::getSingleton('core/layout')->
				createBlock("core/template")->
				setTemplate("zolagodropship/vendor/helper/form/design.phtml")->
				setElement($this);
		return $block->toHtml();
	}

}