<?php

/**
 * Class Zolago_Adminhtml_Block_Customer_Edit_Tab_Offline
 */
class Zolago_Adminhtml_Block_Customer_Edit_Tab_Offline extends Mage_Adminhtml_Block_Widget_Form
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
            ->setFormCode('adminhtml_gh_offline')
            ->initDefaultValues();

        $fieldset = $form->addFieldset('account_offline_fieldset', array(
            'legend' => Mage::helper('customer')->__('Loyalty card numbers')
        ));

        $attributes = $customerForm->getAttributes();
        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Eav_Model_Entity_Attribute */
            $attribute->setFrontendLabel(Mage::helper('zolagoadminhtml')->__($attribute->getFrontend()->getLabel()));
            $attribute->unsIsVisible();
        }

        $this->_setFieldset($attributes, $fieldset);

        $form->setValues($customer->getData());
        $this->setForm($form);
        return $this;
    }

	/**
	 * Add in dummy way the spacer between
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	protected function _getAdditionalElementHtml($element) {
		if (in_array($element->getData('name'), array(
			'loyalty_card_number_1_expire',
			'loyalty_card_number_2_expire',
			'loyalty_card_number_3_expire'))) {
			return "<br /><br /><br />";
		}
	}


}
