<?php

/**
 * Class Zolago_Adminhtml_Block_Customer_Edit_Tab_Offline
 */
class Zolago_Adminhtml_Block_Customer_Edit_Tab_Ghutm extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Initialize block
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Initialize form
     *
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Account
     */
    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_account_offline_data');
        $form->setFieldNameSuffix('account_offline_data');

        $customer = Mage::registry('current_customer');

        /** @var $customerForm Mage_Customer_Model_Form */
        $customerForm = Mage::getModel('customer/form');
        $customerForm->setEntity($customer)
            ->setFormCode('adminhtml_ghutm')
            ->initDefaultValues();

        $translateHelper = Mage::helper('ghutm');
        
        $fieldset = $form->addFieldset('ghutm_fieldset', array(
            'legend' => $translateHelper->__('Traffic source')
        ));

        $utmData = $customer->getUtmData() ? json_decode($customer->getUtmData(),1) : false;
        if(!$utmData) {
            $fieldset->addField('utm_data_empty', 'text', array(
                'label' => $translateHelper->__("No data"),
                'style' => 'border:none;width:100%',
                'readonly' => true
            ));
        } else {
            foreach($utmData as $utmName=>$utmValue) {
                $fieldset->addField($utmName, 'text', array(
                    'label' => $utmName,
                    'value' => $utmValue,
                    'readonly' => true,
                    'style' => 'border:none;width:100%',
                ));
            }
        }

        $this->setForm($form);
        return $this;

    }
}
