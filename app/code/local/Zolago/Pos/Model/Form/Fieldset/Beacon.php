<?php
/**
 * builder for contact fieldset
 */
class Zolago_Pos_Model_Form_Fieldset_Beacon extends Zolago_Common_Model_Form_Fieldset_Abstract
{
    protected function _getHelper() {
        return Mage::helper('ghbeacon');
    }

    protected function _addFieldBeaconId() {
        $this->_fieldset->addField('beacon_id', 'text', array(
            'name'          => 'beacon_id',
            'label'         => $this->_helper->__('Beacon ID'),
            'required'      => false,
            "maxlength"     => 64,
            'class'		   => "form-control"
        ));

    }

    protected function _addFieldBeaconName() {
        $this->_fieldset->addField('beacon_name', 'text', array(
            'name'          => 'beacon_name',
            'label'         => $this->_helper->__('Beacon name'),
            'required'      => false,
            "maxlength"     => 128,
            'class'		   => "form-control"
        ));

    }
}