<?php
/**
 * ZolagoOs LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   ZolagoOs
 * @package    ZolagoOs_OmniChannel
 * @copyright  Copyright (c) 2008-2009 ZolagoOs LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('vendor_form');
        //$this->setTemplate('udropship/vendor/form.phtml');
    }

    protected function _prepareForm()
    {
        $vendor = Mage::registry('vendor_data');
        $hlp = Mage::helper('udropship');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('vendor_form', array(
            'legend'=>$hlp->__('Vendor Info')
        ));

        $fieldset->addField('reg_id', 'hidden', array(
            'name'      => 'reg_id',
        ));
        $fieldset->addField('password_hash', 'hidden', array(
            'name'      => 'password_hash',
        ));
        $fieldset->addField('save_continue', 'hidden', array(
            'name'      => 'save_continue',
        ));

        $fieldset->addField('vendor_name', 'text', array(
            'name'      => 'vendor_name',
            'label'     => $hlp->__('Vendor Name'),
            'class'     => 'required-entry',
            'required'  => true,
        ));

        if (Mage::helper('udropship')->isModuleActive('udmspro')) {
            $fieldset->addType('udvendor_status', Mage::getConfig()->getBlockClassName('udropship/adminhtml_vendor_helper_form_dependSelect'));
            $udVendorStatusType = 'udvendor_status';
            $udVendorStatusFC = array(
                'depend_fields' => array(
                    'reject_reason' => 'R',
                    'send_reject_email' => 'R',
                    'send_confirmation_email' => 'A'
                )
            );
        } else {
            $udVendorStatusFC = array();
            $udVendorStatusType = 'select';
        }

        $regulationAccepted = (int)($vendor && $vendor->getRegulationAccepted());

        //pozwalamy na przełączenie statusu na aktywny - gdy regulamin zaakceptowany
        $fieldset->addField('status', $udVendorStatusType, array(
            'name' => 'status1',
            'label' => $hlp->__('Status'),
            'class' => 'required-entry',
            'required' => true,
            'options' => Mage::getSingleton('udropship/source')->setPath('vendor_statuses')->toOptionHash(),
            'field_config' => $udVendorStatusFC,
            'onchange' => 'checkSelectedItem(this.value)',
            'note' => $hlp->__("Status can be set to Active if regulations accepted")

        ))
            ->setAfterElementHtml("
                         <script type=\"text/javascript\">
                            var regulationAccepted = '" . $regulationAccepted . "';

                            if(regulationAccepted == 0){
                                jQuery(\"[name=status1] option[value='A']\").prop(\"disabled\", true);
                            }
                         </script>");

        if (Mage::helper('udropship')->isModuleActive('udmspro')) {
            $fieldset->addField('reject_reason', 'textarea', array(
                'name'      => 'reject_reason',
                'label'     => $hlp->__('Reject Reason'),
                'class'     => 'required-entry',
                'required'  => true,
                'style'     => 'height:100px',
            ));
            $fieldset->addField('send_reject_email', 'select', array(
                'name'      => 'send_reject_email',
                'label'     => $hlp->__('Send Reject Email'),
                'options'   => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(true),
            ));
        }

        $fieldset->addField('carrier_code', 'select', array(
            'name'      => 'carrier_code',
            'label'     => $hlp->__('Preferred Carrier'),
            'class'     => 'required-entry',
            'required'  => true,
            'options'   => Mage::getSingleton('udropship/source')->setPath('carriers')->toOptionHash(true),
        ));

        $fieldset->addField('use_rates_fallback', 'select', array(
            'name'      => 'use_rates_fallback',
            'label'     => $hlp->__('Use Rates Fallback Chain'),
            'class'     => 'required-entry',
            'required'  => true,
            'options'   => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(true),
            'note'      => $hlp->__('Will try to find available estimate rate for dropship shipping methods in order <br>1. Estimate Carrier <br>2. Override Carrier <br>3. Default Carrier'),
        ));

        $fieldset->addField('email', 'text', array(
            'name'      => 'email',
            'label'     => $hlp->__('Vendor Email'),
            'class'     => 'required-entry validate-email',
            'required'  => true,
            'note'      => $hlp->__('Email is also used as username'),
        ));

        $fieldset->addField('password', 'password', array(
            'name'      => 'password',
            'label'     => $hlp->__('New Password'),
            'class'     => 'validate-password',
            'note'      => $hlp->__('Leave empty for no change'),
        ));
/*
        $fieldset->addField('password', 'password', array(
            'name'      => 'password',
            'label'     => $hlp->__('Log In Password'),
            'note'      => $hlp->__('Login disabled if empty'),
        ));
*/
        $fieldset->addField('telephone', 'text', array(
            'name'      => 'telephone',
            'label'     => $hlp->__('Vendor Telephone'),
            'note'      => $hlp->__('Phone number is required for FedEx label printing'),
        ));

        $fieldset->addField('fax', 'text', array(
            'name'      => 'fax',
            'label'     => $hlp->__('Vendor Fax'),
        ));

        $templates = Mage::getSingleton('adminhtml/system_config_source_email_template')->toOptionArray();
        $templates[0]['label'] = $hlp->__('Use Default Configuration');
        $fieldset->addField('email_template', 'select', array(
            'name'      => 'email_template',
            'label'     => $hlp->__('Notification Template'),
            'values'   => $templates,
        ));

        $fieldset->addField('vendor_shipping', 'hidden', array(
            'name' => 'vendor_shipping',
        ));
        $fieldset->addField('vendor_products', 'hidden', array(
            'name' => 'vendor_products',
        ));

        if (Mage::getStoreConfigFlag('udropship/customer/allow_shipping_extra_charge')) {
            $fieldset->addField('allow_shipping_extra_charge', 'select', array(
                'name'      => 'allow_shipping_extra_charge',
                'label'     => $hlp->__('Allow shipping extra charge'),
                'options'   => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(true),
            ));
            $fieldset->addField('default_shipping_extra_charge_suffix', 'text', array(
                'name'      => 'default_shipping_extra_charge_suffix',
                'label'     => $hlp->__('Default shipping extra charge suffix'),
            ));
            $fieldset->addField('default_shipping_extra_charge_type', 'select', array(
                'name'      => 'default_shipping_extra_charge_type',
                'label'     => $hlp->__('Default shipping extra charge type'),
                'options'   => Mage::getSingleton('udropship/source')->setPath('shipping_extra_charge_type')->toOptionHash(true),
            ));
            $fieldset->addField('default_shipping_extra_charge', 'text', array(
                'name'      => 'default_shipping_extra_charge',
                'label'     => $hlp->__('Default shipping extra charge'),
            ));
            $fieldset->addField('is_extra_charge_shipping_default', 'select', array(
                'name'      => 'is_extra_charge_shipping_default',
                'label'     => $hlp->__('Is extra charge shipping default'),
                'options'   => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(true),
            ));
        }

/*
        $fieldset->addField('url_key', 'text', array(
            'name'      => 'url_key',
            'label'     => $hlp->__('URL friendly identifier'),
        ));
*/
 
 

        Mage::dispatchEvent('udropship_adminhtml_vendor_edit_prepare_form', array('block'=>$this, 'form'=>$form, 'id'=>$id));

        if ($vendor) {
            if ($this->getRequest()->getParam('reg_id')) {
                $shipping = array();
                foreach ($vendor->getShippingMethods() as $sId=>$_s) {
                    foreach ($_s as $s) {
                        $shipping[$sId][] = array(
                            'on' => 1,
                            'est_carrier_code' => $s['est_carrier_code'],
                            'carrier_code' => $s['carrier_code'],
                        );
                    }
                }
                $vendor->setVendorShipping(Zend_Json::encode($shipping));
                $vendor->setSendConfirmationEmail(!Mage::getStoreConfigFlag('udropship/microsite/skip_confirmation'));
            }
            $form->setValues($vendor->getData());
        }


        return parent::_prepareForm();
    }

}