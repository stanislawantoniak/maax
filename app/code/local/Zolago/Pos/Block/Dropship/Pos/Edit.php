<?php
class Zolago_Pos_Block_Dropship_Pos_Edit extends Mage_Core_Block_Template {
	
	protected function _construct() {
		parent::_construct();
		$helper = Mage::helper('zolagopos');
        $form = Mage::getModel('zolagodropship/form');
		/* @var $form Zolago_Dropship_Model_Form */
		
        $form->setAction($this->getUrl("udropship/pos/save"));
		
        $settings = $form->addFieldset('setting', array('legend'=>$helper->__('POS Settings')));
        
        $builder = Mage::getModel('zolagopos/form_fieldset_settings'); 
        $builder->setFieldset($settings);
        
        $builder->prepareForm(array(
            'name',
            'is_active',
            'minimal_stock',
            'priority',
            'external_id',
            'client_number',
        ));         
        
		$settings->addField("pos_id", "hidden", array("name"=>"pos_id"));
		        
        $address = $form->addFieldset('address', array('legend'=>$helper->__('Address')));
        $builder = Mage::getModel('zolagopos/form_fieldset_address'); 
        $builder->setFieldset($address);
        $builder->setModel($this->getModel());
        $builder->prepareForm(array(
            'city',
            'country_id',
            'region_id',
            'street',
            'postcode',
            'company'
        ));
                            
        $contact = $form->addFieldset('contact', array('legend'=>$helper->__('Contact')));
        $builder = Mage::getModel('zolagopos/form_fieldset_contact'); 
        $builder->setFieldset($contact);
        $builder->prepareForm(array(
            'phone',
            'email',
        ));
		
        $dhl = $form->addFieldset('dhl', array('legend'=>$helper->__('DHL Settings')));
        $builder = Mage::getModel('zolagopos/form_fieldset_dhl'); 
        $builder->setFieldset($dhl);
        $builder->prepareForm(array(
            'use_dhl',
            'dhl_login',
            'dhl_password',
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
		if(!Mage::registry("current_pos")){
			 Mage::register("current_pos", Mage::getModel("zolagopos/pos"));
		}
		return Mage::registry("current_pos");
	}
	
	public function getIsNew() {
		return $this->getModel()->getId();
	}
	
	
}

