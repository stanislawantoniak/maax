<?php

/**
 * Form for integration config like:
 * GH api
 * Modago integrator
 *
 * Class GH_Api_Block_Adminhtml_Dropship_Settings_Ghapi_Form
 */
class GH_Api_Block_Adminhtml_Dropship_Settings_Ghapi_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('vendor_form');
    }
    protected function _prepareForm()
    {
        /** @var Zolago_Dropship_Model_Vendor $vendor */
        $vendor = Mage::registry('vendor_data');
        /** @var GH_Api_Helper_Data $hlp */
        $hlp = Mage::helper('ghapi');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        // GH API
        $fieldset = $form->addFieldset('ghapi_vendor_access', array(
            'legend'=>$hlp->__('GH API')
        ));
        $fieldset->addField('ghapi_vendor_access_allow', 'select', array(
            'name'      => 'ghapi_vendor_access_allow',
            'label'     => $hlp->__('GH API Access'),
            'options'   => Mage::getSingleton('ghapi/source')->setPath('yesno')->toOptionHash(),
        ));
        // IAI
        if (Mage::helper('core')->isModuleEnabled('ZolagoOs_IAIShop')) {
            $fieldset = $form->addFieldset('zosiaishop_vendor_access', array(
                'legend'=>$hlp->__('IAI-Shop API')
            ));
            $fieldset->addField('zosiaishop_vendor_access_allow', 'select', array(
                'name'      => 'zosiaishop_vendor_access_allow',
                'label'     => $hlp->__('IAI-Shop API Access'),
                'options'   => Mage::getSingleton('ghapi/source')->setPath('yesno')->toOptionHash(),
            ));        
        }
        // GH integration
        /** @var GH_Integrator_Helper_Data $hlp */
        $hlp = Mage::helper('ghintegrator');
        $fieldset = $form->addFieldset('modago_integrator', array(
            'legend'    => $hlp->__('Modago Integrator')
        ));
        $fieldset->addField('integrator_enabled', 'select', array(
            'name'      => 'integrator_enabled',
            'label'     => $hlp->__('Is integrator enabled'),
            'options'   => Mage::getSingleton('ghapi/source')->setPath('yesno')->toOptionHash(),
        ));
        $secret = $vendor ? $vendor->getIntegratorSecret() : '';
        $fieldset->addField('integrator_secret', 'note', array(
            'label'     => $hlp->__('Secret key'),
            'text'      => $secret,
        ));
        $last = $vendor ? $vendor->getLastIntegration() : "";
        $fieldset->addField('last_integration', 'note', array(
            'label'     => $hlp->__('Last integration'),
            'text'      => $last
        ));


        /*Sales manago integration*/
		/** @var Zolago_Adminhtml_Helper_Data $hlp */
		$hlp = Mage::helper('zolagoadminhtml');
        $fieldset = $form->addFieldset('modago_salesmanago', array(
            'legend' => $hlp->__('Integration with SALESmanago')
        ));
        $fieldset->addType('salesmanago', 'Zolago_Adminhtml_Block_Renderer_Salesmanago');
        $fieldset->addField('modago_salesmanago_login', 'salesmanago', array(
            'name'      => 'modago_salesmanago_login',
            'label'     => $hlp->__('Integration with SALESmanago')

        ));

        if ($vendor) {
            $form->setValues($vendor->getData());
        }
        return parent::_prepareForm();
    }
}
