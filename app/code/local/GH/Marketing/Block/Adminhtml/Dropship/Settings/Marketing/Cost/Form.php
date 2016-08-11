<?php

/**
 * Class GH_Marketing_Block_Adminhtml_Dropship_Settings_Marketing_Cost_Form
 */
class GH_Marketing_Block_Adminhtml_Dropship_Settings_Marketing_Cost_Form extends Mage_Adminhtml_Block_Widget_Form
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
        $hlp = Mage::helper('ghmarketing');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

		/** @var GH_Marketing_Model_Resource_Marketing_Cost_Type_Collection $marketingCostTypeCollection */
		$marketingCostTypeCollection = Mage::getResourceModel('ghmarketing/marketing_cost_type_collection');
		foreach ($marketingCostTypeCollection as $type) {
			/** @var GH_Marketing_Model_Marketing_Cost_Type $type */
			$fieldsetAdditional = $form->addFieldset('fieldset_marketing_cost_type_' . $type->getCode(), array(
				'legend' => $hlp->__('Marketing cost for %s', $type->getName())
			));
			$fieldSelect = 'marketing_cost_type_' . $type->getCode();
			$fieldsetAdditional->addField($fieldSelect, 'select', array(
				'name' => $fieldSelect,
				'label' => $hlp->__('Method of calculating cost'),
				'values' => Mage::getModel('ghmarketing/source_marketing_cost_type_option')->toOptionArray()
			));
			$fieldCpc = 'marketing_cost_type_'. $type->getCode() . '_cpc';
			$fieldsetAdditional->addField($fieldCpc, 'text', array(
				'name'               => $fieldCpc,
				'label'              => $hlp->__('CPC (cost per click)'),
				'class'              => 'validate-number',
			));
			$fieldCps = 'marketing_cost_type_'. $type->getCode() . '_cps';
			$fieldsetAdditional->addField($fieldCps, 'text', array(
				'name'               => $fieldCps,
				'label'              => $hlp->__('CPS (cost per sale)'),
				'class'              => 'validate-number',
			));
			$fieldFixed = 'marketing_cost_type_'. $type->getCode() . '_cpa';
			$fieldsetAdditional->addField($fieldFixed, 'text', array(
				'name'               => $fieldFixed,
				'label'              => $hlp->__('CPA (cost per action)'),
				'class'              => 'validate-number',
			));
		}

        if ($vendor) {
            $form->setValues($vendor->getData());
        }
        return parent::_prepareForm();
    }
}
