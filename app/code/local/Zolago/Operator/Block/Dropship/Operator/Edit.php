<?php
class Zolago_Operator_Block_Dropship_Operator_Edit extends Mage_Core_Block_Template {
	
	protected function _construct() {
		parent::_construct();
		$helper = Mage::helper('zolagooperator');
        $form = Mage::getModel('zolagodropship/form'); 
		
        $form->setAction($this->getUrl("udropship/operator/save"));
		
                            
        $contact = $form->addFieldset('contact', array('legend'=>$helper->__('Details')));
        $builder = Mage::getModel('zolagooperator/form_fieldset_details');
        $builder->setFieldset($contact);
        $builder->prepareForm(array(
            'email',
            'password',
            'password_confirm',
            'is_active',
            'firstname',
            'lastname',
            'phone',
        ));
		
		if($this->getIsNew()){
			$form->getElement("password")->setRequired(true);
			$form->getElement("password_confirm")->setRequired(true);
		}
        
        $contact->addField("operator_id", "hidden", array("name"=>"operator_id"));		
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
		return !(bool)$this->getModel()->getId();
	}
	
	
}

