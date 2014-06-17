<?php
class Zolago_Rma_Block_Adminhtml_Dropship_Edit_Tab_ReturnReasons 
	extends Mage_Adminhtml_Block_Widget_Form{
	
	public function __construct(){
		
		parent::_construct();
		$this->setDestElementId('vendor_returnreasons');
		
	}
	
	protected function _prepareForm()
    {
        $return_reason = Mage::registry('return_reason');
        $helper = Mage::helper('zolagorma');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('returnreasons', array(
            'legend'=>$helper->__('Return Reasons')
        ));

        $fieldset->addType('return_reasons', 'Zolago_Rma_Block_Adminhtml_Rma_Edit_Renderer_ReturnReasons');
        
        $fieldset->addField('return_reasons', 'return_reasons', array(
            'name'      => 'return_reasons',
            'label'     => $helper->__('Return reasons'),
        ));


        if ($return_reason) {
            $form->setValues($return_reason->getData());
        }

        return parent::_prepareForm();
    }
	
}
