<?php
class Zolago_Operator_Block_Dropship_Operator_Edit extends Mage_Core_Block_Template {
	
	protected function _construct() {
		parent::_construct();
		$helper = Mage::helper('zolagooperator');
        $form = new Zolago_Dropship_Block_Form();

		
        $form->setAction($this->getUrl("udropship/operator/save"));
		
                            
        $contact = $form->addFieldset('contact', array('legend'=>$helper->__('Details')));
        $builder = new Zolago_Operator_Helper_Form_Fieldset_Details($contact);
        $builder->prepareForm(array(
            'email',
            'password',
            'password_confirm',
            'is_active',
            'firstname',
            'lastname',
            'phone',
        ));
        
		
        $form->setValues($this->getModel()->getData());
        $this->setForm($form);
	}

	public function getFormHtml() {
		return $this->getForm()->toHtml();
	}
	
	/**
	 * @return Zolago_Pos_Model_Pos
	 */
	public function getModel() {
		if(!Mage::registry("current_operator")){
			 Mage::register("current_operator", Mage::getModel("zolagooperator/operator"));
		}
		return Mage::registry("current_operator");
	}
	
	public function getIsNew() {
		return $this->getModel()->getId();
	}
	
	
}

