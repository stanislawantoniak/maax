<?php
/**
 * organize vendor tabs
 */

class Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Addresses extends Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Abstract {
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('address_form');
        $this->configKey = 'address';
    }    
    
    
    /**
     * prepare form
     */
     protected function _prepareForm() {
         $this->_readFieldsetFromXml();
         $this->_prepareAddressFields();
         Mage_Adminhtml_Block_Widget_Form::_prepareForm();
     }
     protected function _prepareAddressFields() {
 
        $vendor = Mage::registry('vendor_data');
        $hlp = Mage::helper('udropship');
        $id = $this->getRequest()->getParam('id');
        $form = $this->getForm();
        $countries = Mage::getModel('adminhtml/system_config_source_country')
            ->toOptionArray();
        //unset($countries[0]);


        $countryId = Mage::registry('vendor_data') ? Mage::registry('vendor_data')->getCountryId() : null;
        if (!$countryId) {
            $countryId = Mage::getStoreConfig('general/country/default');
        }

        $regionCollection = Mage::getModel('directory/region')
            ->getCollection()
            ->addCountryFilter($countryId);

        $regions = $regionCollection->toOptionArray();

        if ($regions) {
            $regions[0]['label'] = $hlp->__('Please select state...');
        } else {
            $regions = array(array('value'=>'', 'label'=>''));
        }

 
 
        $fieldset = $form->addFieldset('address_form', array(
            'legend'=>$hlp->__('Shipping Origin Address')
        ));

         $fieldset->addField('rma_company_name', 'text', array(
             'name'      => 'rma_company_name',
             'label'     => $hlp->__('Company name'),
         ));
//         $fieldset->addField('tax_no', 'text', array(
//             'name'      => 'tax_no',
//             'label'     => $hlp->__('NIP'),
//         ));
//        $fieldset->addField('vendor_attn', 'text', array(
//            'name'      => 'vendor_attn',
//            'label'     => $hlp->__('Attention To'),
//        ));

        $fieldset->addField('street', 'textarea', array(
            'name'      => 'street',
            'label'     => $hlp->__('Street'),
            'class'     => 'required-entry',
            'required'  => true,
            'style'     => 'height:50px',
        ));

        $fieldset->addField('city', 'text', array(
            'name'      => 'city',
            'label'     => $hlp->__('City'),
            'class'     => 'required-entry',
            'required'  => true,
        ));

        $fieldset->addField('zip', 'text', array(
            'name'      => 'zip',
            'label'     => $hlp->__('Zip / Postal code'),
        ));

        $country = $fieldset->addField('country_id', 'select',
            array(
                'name' => 'country_id',
                'label' => $hlp->__('Country'),
                'title' => $hlp->__('Please select Country'),
                'class' => 'required-entry',
                'required' => true,
                'values' => $countries,
            )
        );

        $fieldset->addField('region_id', 'select',
            array(
                'name' => 'region_id',
                'label' => $hlp->__('State'),
                'title' => $hlp->__('Please select State'),
                'values' => $regions,
            )
        );

        $bCountryId = Mage::registry('vendor_data') ? Mage::registry('vendor_data')->getBillingCountryId() : null;
        if (!$bCountryId) {
            $bCountryId = Mage::getStoreConfig('general/country/default');
        }
        $fieldset = $form->addFieldset('billing_form', array(
            'legend'=>$hlp->__('Billing Address')
        ));

        $fieldset->addType('billing_use_shipping', Mage::getConfig()->getBlockClassName('udropship/adminhtml_vendor_helper_form_dependSelect'));

        $fieldset->addField('billing_use_shipping', 'billing_use_shipping', array(
            'name'      => 'billing_use_shipping',
            'label'     => $hlp->__('Same as Shipping'),
            'options'   => Mage::getSingleton('udropship/source')->setPath('billing_use_shipping')->toOptionHash(),
            'field_config' => array(
                'depend_fields' => array(
                    'billing_vendor_attn' => '0',
                    'billing_street' => '0',
                    'billing_city' => '0',
                    'billing_zip' => '0',
                    'billing_country_id' => '0',
                    'billing_region_id' => '0',
                    'billing_email' => '0',
                    'billing_telephone' => '0',
                    'billing_fax' => '0',
                )
            )
        ));

        $fieldset->addField('billing_vendor_attn', 'text', array(
            'name'      => 'billing_vendor_attn',
            'label'     => $hlp->__('Attention To'),
            'note'      => $hlp->__('Leave empty to use shipping origin'),
        ));

        $fieldset->addField('billing_street', 'textarea', array(
            'name'      => 'billing_street',
            'label'     => $hlp->__('Street'),
            'class'     => 'required-entry',
            'required'  => true,
            'style'     => 'height:50px',
        ));

        $fieldset->addField('billing_city', 'text', array(
            'name'      => 'billing_city',
            'label'     => $hlp->__('City'),
            'class'     => 'required-entry',
            'required'  => true,
        ));

        $fieldset->addField('billing_zip', 'text', array(
            'name'      => 'billing_zip',
            'label'     => $hlp->__('Zip / Postal code'),
        ));

        $bCountry = $fieldset->addField('billing_country_id', 'select',
            array(
                'name' => 'billing_country_id',
                'label' => $hlp->__('Country'),
                'title' => $hlp->__('Please select Country'),
                'class' => 'required-entry',
                'required' => true,
                'values' => $countries,
            )
        );

        $fieldset->addField('billing_region_id', 'select',
            array(
                'name' => 'billing_region_id',
                'label' => $hlp->__('State'),
                'title' => $hlp->__('Please select State'),
                'values' => $regions,
            )
        );

        $fieldset->addField('billing_email', 'text', array(
            'name'      => 'billing_email',
            'label'     => $hlp->__('Email'),
            'class'     => 'validate-email',
            'note'      => $hlp->__('Leave empty to use default'),
        ));

        $fieldset->addField('billing_telephone', 'text', array(
            'name'      => 'billing_telephone',
            'label'     => $hlp->__('Telephone'),
            'note'      => $hlp->__('Leave empty to use default'),
        ));

        $fieldset->addField('billing_fax', 'text', array(
            'name'      => 'billing_fax',
            'label'     => $hlp->__('Fax'),
            'note'      => $hlp->__('Leave empty to use default'),
        ));

//        Mage::dispatchEvent('udropship_adminhtml_vendor_edit_prepare_form', array('block'=>$this, 'form'=>$form, 'id'=>$id));

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
                $vendor->setSendConfirmationEmail(!Mage::getStoreConfigFlag('zolagoos/microsite/skip_confirmation'));
            }
            $form->setValues($vendor->getData());
        }

        if (!$id) {
            $country->setValue($countryId);
            $bCountry->setValue($bCountryId);
        }
     }

}