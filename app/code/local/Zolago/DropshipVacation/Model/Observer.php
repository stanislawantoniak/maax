<?php

class Zolago_DropshipVacation_Model_Observer extends Unirgy_DropshipVacation_Model_Observer
{

    public function udropship_adminhtml_vendor_edit_prepare_form($observer)
    {
        // Removed Field: Vacation Mode
        // Refs#1046
    }

    public function zolago_adminhtml_vendor_edit_custom_prepare_form_fieldset_vendor_info($observer) {
        // Moved Field: Vacation Mode
        // Refs#1046
        $form = $observer->getForm();
        $vForm = $form->getElement('vendor_form');
        if ($vForm) {
            $hlp = Mage::helper('udropship');
            $vForm->addType('vacation_mode', Mage::getConfig()->getBlockClassName('udropship/adminhtml_vendor_helper_form_dependSelect'));
            $vForm->addField('vacation_mode', 'vacation_mode', array(
                'name'      => 'vacation_mode',
                'label'     => $hlp->__('Vacation Mode'),
                'options'   => Mage::getSingleton('udvacation/source')->setPath('vacation_mode')->toOptionHash(),
                'field_config' => array(
                    'depend_fields' => array(
                        'vacation_end' => '1,2',
                    )
                )
            ));
            $vForm->addField('vacation_end', 'date', array(
                'name'      => 'vacation_end',
                'image' => Mage::getDesign()->getSkinUrl('images/grid-cal.gif'),
                'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                'format' => Varien_Date::DATE_INTERNAL_FORMAT,
                'label'     => $hlp->__('Vacation Ends At'),
            ));
        }
    }
}