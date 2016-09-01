<?php
/**
 * builder for settings fieldset
 */
class Zolago_Pos_Model_Form_Fieldset_Settings extends Zolago_Common_Model_Form_Fieldset_Abstract {
    
    protected function _getHelper() {
        return Mage::helper('zolagopos');
    }

    protected function _addFieldName() {
        $this->_fieldset->addField('name', 'text', array(
                                       'name'          => 'name',
                                       'label'         => $this->_helper->__('Name'),
                                       'required'      => true,
                                       "maxlength"     => 100,
									   'class'		   => "form-control",
                                   ));

    }
    protected function _addFieldVendorOwnerId() {
        $this->_fieldset->addField('vendor_owner_id', 'select', array(
                                       'name'          => 'vendor_owner_id',
                                       'label'         => $this->_helper->__('Vendor owner'),
                                       'values'        => Mage::getSingleton("udropship/vendor_source")->getAllOptions(),
									   'class'		   => "form-control",
                                   ));

    }
    protected function _addFieldExternalId() {
        $this->_fieldset->addField('external_id', 'text', array(
                                       'name'          => 'external_id',
                                       'label'         => $this->_helper->__('External ID'),
                                       "maxlength"     => 100,
                                       "class"         => "form-control",
                                   ));
    }

    protected function _addFieldIsAvailableAsPickupPoint()
    {
        $this->_fieldset->addField('is_available_as_pickup_point',
            'checkbox',
            array(
                'name' => 'is_available_as_pickup_point',
                'label' => $this->_helper->__('Is POS available as Pick-Up Point'),
                'class' => 'toggle bootstrapSwitch',
                "use_plugin" => "switch",
            ));
    }

}
