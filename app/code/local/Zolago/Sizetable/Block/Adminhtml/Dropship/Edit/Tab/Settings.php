<?php
class Zolago_Sizetable_Block_Adminhtml_Dropship_Edit_Tab_Settings 
	extends Mage_Adminhtml_Block_Widget_Form{
	
	public function __construct(){
		
		parent::_construct();
		
	}
	protected function _prepareFormBrand() {
        $helper = Mage::helper('zolagosizetable');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('sizetable_vendor_brand', array(
            'legend'=>$helper->__('Available vendor brands')
        ));

        $fieldset->addType('sizetable_vendor_brand', 'Zolago_Sizetable_Block_Adminhtml_Dropship_Edit_Form_Element_Brand');
        
        $fieldset->addField('sizetable_vendor_brand_field', 'sizetable_vendor_brand', array(
            'name'      => 'sizetable_vendor_brand',
            'label'     => $helper->__('Available vendor brands'),
        ));
	}
	protected function _prepareFormAttributeSet() {
        $helper = Mage::helper('zolagosizetable');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('sizetable_vendor_attributeset', array(
            'legend'=>$helper->__('Available vendor attribute sets')
        ));

        $fieldset->addType('sizetable_vendor_attributeset', 'Zolago_Sizetable_Block_Adminhtml_Dropship_Edit_Form_Element_Attributeset');
        
        $fieldset->addField('sizetable_vendor_attributeset_field', 'sizetable_vendor_attributeset', array(
            'name'      => 'sizetable_vendor_attributeset',
            'label'     => $helper->__('Available vendor attribute sets'),
        ));
	
	}
	protected function _prepareForm()
    {
        $this->_prepareFormBrand();
        $this->_prepareFormAttributeSet();
        return parent::_prepareForm();
    }
	
}
