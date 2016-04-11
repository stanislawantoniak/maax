<?php

/**
 * @see Unirgy_DropshipTierCommission_Block_Adminhtml_VendorEditTab_ComRates_Form
 *      in: app/code/community/Unirgy/DropshipTierCommission/Block/Adminhtml/VendorEditTab/ComRates/Form.php
 * Class Zolago_DropshipTierCommission_Block_Adminhtml_VendorEditTab_ComRates_Form
 */
class Zolago_DropshipTierCommission_Block_Adminhtml_VendorEditTab_ComRates_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('vendor_tiercom');
    }

    protected function _prepareForm()
    {
        $vendor = Mage::registry('vendor_data');
        $hlp = Mage::helper('udropship');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

		// Fieldset #1
        $fieldset = $form->addFieldset('tiercom', array(
            'legend'=>$hlp->__('Basic rates definition')
        ));

        $fieldset->addType('tiercom_rates', Mage::getConfig()->getBlockClassName('udtiercom/adminhtml_vendorEditTab_comRates_form_rates'));

        $fieldset->addField('tiercom_rates', 'tiercom_rates', array(
            'name'      => 'tiercom_rates',
            'label'     => $hlp->__('Rates'),
        ));

        $fieldset->addField('commission_percent', 'text', array(
            'name'      => 'commission_percent',
            'label'     => $hlp->__('Default Commission Percent'),
            'after_element_html' => $hlp->__('<br />Default value: %.2F. Leave empty to use default.', Mage::getStoreConfig('udropship/tiercom/commission_percent'))
        ));

        // Added
        $fieldset->addField('sale_commission_percent', 'text', array(
            'name'      => 'sale_commission_percent',
            'label'     => $hlp->__('Default Commission Percent for product with flag SALE'),
            'after_element_html' => $hlp->__('<br />Default value: %.2F. Leave empty to use default.', Mage::getStoreConfig('udropship/tiercom/sale_commission_percent'))
        ));

		$fieldset->addField('terminal_percent_for_charge_sale_commission', 'text', array(
			'name'      => 'terminal_percent_for_charge_sale_commission',
			'label'     => $hlp->__('Terminal percent for charge sale commission'),
			'after_element_html' => '<br />' . $hlp->__('Commission for the sale will be calculated for products which have a lower selling price of n-percent strikeout price. Leave empty for default')
		));

		// Fieldset #2
        $fieldsetAdditional = $form->addFieldset('tiercom_additional', array(
            'legend'    => $hlp->__('Additional rates definition')
        ));
        $fieldsetAdditional->addField('marketing_charges_enabled', 'select', array(
            'name'      => 'marketing_charges_enabled',
            'label'     => $hlp->__('Marketing charges enabled'),
            'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
        ));
        $fieldsetAdditional->addField('cpc_commission', 'text', array(
            'name'      => 'cpc_commission',
            'label'     => $hlp->__('CPC commission percent'),
            'after_element_html' => $hlp->__('<br />Default value: %.2F. Leave empty to use default.', Mage::getStoreConfig('udropship/tiercom/commission_percent'))
        ));

        if ($vendor) {
            $form->setValues($vendor->getData());
        }

        return parent::_prepareForm();

    }

}