<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Shipping_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('shipping_form');
        //$this->setTemplate('udropship/vendor/form.phtml');
    }

    protected function _prepareForm()
    {
        $cert = Mage::registry('shipping_data');
        $hlp = Mage::helper('udropship');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('shipping_form', array(
            'legend'=>$hlp->__('Shipping Info')
        ));

        $fieldset->addField('shipping_code', 'text', array(
            'name'      => 'shipping_code',
            'label'     => $hlp->__('Shipping Method Code'),
            'class'     => 'required-entry',
            'required'  => true,
        ));

        $fieldset->addField('shipping_title', 'text', array(
            'name'      => 'shipping_title',
            'label'     => $hlp->__('Shipping Method Title'),
            'class'     => 'required-entry',
            'required'  => true,
        ));


        $fieldset->addField('days_in_transit', 'text', array(
            'name'      => 'days_in_transit',
            'label'     => $hlp->__('Days In Transit'),
            'class'     => 'required-entry',
            'required'  => true,
        ));

        $options = Mage::getSingleton('adminhtml/system_config_source_website')->toOptionArray();
        array_unshift($options, array('label'=>'All websites', 'value'=>0));
        $fieldset->addField('website_ids', 'multiselect', array(
            'name'      => 'website_ids[]',
            'label'     => $this->__('Websites'),
            'title'     => $this->__('Websites'),
            'required'  => true,
            'values'    => $options,
        ));

        Mage::dispatchEvent('udropship_adminhtml_shipping_edit_prepare_form', array('block'=>$this, 'form'=>$form, 'id'=>$id));

        if (Mage::registry('shipping_data')) {
            $form->setValues(Mage::registry('shipping_data')->getData());
        }

        return parent::_prepareForm();
    }

}