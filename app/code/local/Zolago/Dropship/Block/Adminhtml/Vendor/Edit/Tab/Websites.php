<?php

/**
 * organize vendor tabs
 */
class Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Websites extends Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('websites_form');
        $this->configKey = 'websites';
    }


    /**
     * prepare form
     */
    protected function _prepareForm()
    {
        $this->_readFieldsetFromXml();
        $this->_prepareFields();
        Mage_Adminhtml_Block_Widget_Form::_prepareForm();
    }

    protected function _prepareFields()
    {
        $vendor = Mage::registry('vendor_data');
        $hlp = Mage::helper('udropship');

        $form = $this->getForm();


        $fieldset = $form->addFieldset('websites_form', array(
            'legend' => $hlp->__('Websites allowed')
        ));
        $websites = array();
        $collection = Mage::getModel("core/resource_website_collection");
        foreach ($collection as $collectionItem) {
            $websites[] = array("label" => $collectionItem->getData("name"), "value" => $collectionItem->getData("website_id"));

        }

        $fieldset->addField('websites_allowed', 'multiselect', array(
            'name' => 'websites_allowed',
            'label' => $hlp->__('Websites'),
            "values" => $websites
        ));

        if ($vendor) {
            $form->setValues($vendor->getData());
        }
    }

}