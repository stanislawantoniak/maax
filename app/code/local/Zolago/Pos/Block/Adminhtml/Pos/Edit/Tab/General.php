<?php
class Zolago_Pos_Block_Adminhtml_Pos_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{


    public function canShowTab() {
        return 1;
    }

    public function getTabLabel() {
        return Mage::helper('zolagopos')->__("General");
    }

    public function getTabTitle() {
        return Mage::helper('zolagopos')->__("General POS Information");
    }

    public function isHidden() {
        return false;
    }

    protected function _prepareForm()
    {
        $helper = Mage::helper('zolagopos');
        $form = new Varien_Data_Form();
        
        $settings = $form->addFieldset('setting', array('legend'=>$helper->__('POS Settings')));

        $builder = Mage::getModel('zolagopos/form_fieldset_settings'); 
        $builder->setFieldset($settings);
        
        
        $builder->prepareForm(array(
            'name',
            'is_active',
            'vendor_owner_id',
            'minimal_stock',
            'priority',
            'external_id',
        ));         

        
        
        
        $address = $form->addFieldset('address', array('legend'=>$helper->__('Address')));
        $builder = Mage::getModel('zolagopos/form_fieldset_address'); 
        $builder->setFieldset($address);
        $builder->setModel($this->_getModel());
        $builder->prepareForm(array(
            'city',
            'country_id',
            'region_id',
            'street',
            'postcode',
            'company',
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
            'dhl_account',
            'dhl_login',
            'dhl_password',
        ));
        
        
        $form->setValues($this->_getValues());
        
        $this->setForm($form);
    }
    
    protected function _getValues() {
        return $this->_getModel()->getData();
    }
    
    /**
     * @return Zolago_Pos_Model_Pos
     */
    protected function _getModel() {
        return Mage::registry('zolagopos_current_pos');
    }
    
}
