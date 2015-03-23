<?php

class GH_Api_Block_Adminhtml_Dropship_Settings_Ghapi_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('vendor_form');
    }
    protected function _prepareForm()
    {
        $vendor = Mage::registry('vendor_data');
        $hlp = Mage::helper('ghapi');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('ghapi_vendor_access', array(
            'legend'=>$hlp->__('GH API')
        ));
        $fieldset->addField('ghapi_vendor_access_allow', 'select', array(
            'name'      => 'ghapi_vendor_access_allow',
            'label'     => $hlp->__('GH API Access'),
            'options'   => Mage::getSingleton('ghapi/source')->setPath('yesno')->toOptionHash(),
        ));

        if ($vendor) {
            $form->setValues($vendor->getData());
        }

        return parent::_prepareForm();
    }
}
