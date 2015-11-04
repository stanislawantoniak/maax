<?php

/**
 * builder for settings fieldset
 */
class GH_Statements_Model_Form_Fieldset_Calendar_Item extends Zolago_Common_Model_Form_Fieldset_Abstract
{

    protected function _getHelper()
    {
        return Mage::helper('ghstatements');
    }

    protected function _addFieldEventDate()
    {
        $this->_fieldset->addField('event_date', 'date', array(
            'name' => 'event_date',
            'label' => $this->_helper->__('Event date'),
            'required' => true,
            "maxlength" => 32,
            'class' => "form-control",
            'format' => 'yyyy-MM-dd',
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'time'   => false,
            'after_element_html' => $this->_getHelper()->__('<small>Date format (YYYY-MM-DD)</small>'),
        ));

    }
}
