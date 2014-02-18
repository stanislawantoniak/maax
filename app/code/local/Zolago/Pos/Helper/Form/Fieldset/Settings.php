<?php
/**
 * builder for settings fieldset
 */
class Zolago_Pos_Helper_Form_Fieldset_Settings extends Zolago_Pos_Helper_Form_Fieldset {
    protected function _addFieldName() {
        $this->_fieldset->addField('name', 'text', array(
                                       'name'          => 'name',
                                       'label'         => $this->_helper->__('Name'),
                                       'required'      => true,
                                       "maxlength"     => 100
                                   ));

    }
    protected function _addFieldIsActive() {
        $this->_fieldset->addField('is_active', 'select', array(
                                       'name'          => 'is_active',
                                       'label'         => $this->_helper->__('Is active'),
                                       'required'      => true,
                                       'options'       => Mage::getSingleton("adminhtml/system_config_source_yesno")->toArray()
                                   ));

    }
    protected function _addFieldVendorOwnerId() {
        $this->_fieldset->addField('vendor_owner_id', 'select', array(
                                       'name'          => 'vendor_owner_id',
                                       'label'         => $this->_helper->__('Vendor owner'),
                                       'values'        => Mage::getSingleton("udropship/vendor_source")->getAllOptions(),
                                   ));

    }
    protected function _addFieldMinimalStock() {
        $this->_fieldset->addField('minimal_stock', 'text', array(
                                       'name'          => 'minimal_stock',
                                       'label'         => $this->_helper->__('Minimal stock'),
                                       'required'      => true,
                                       "class"         => "validate-digits",
                                       "maxlength"     => 3
                                   ));
    }
    protected function _addFieldPriority() {
        $this->_fieldset->addField('priority', 'text', array(
                                       'name'          => 'priority',
                                       'label'         => $this->_helper->__('Priority'),
                                       'required'      => true,
                                       "class"         => "validate-digits",
                                       "maxlength"     => 3
                                   ));
    }
    protected function _addFieldExternalId() {
        $this->_fieldset->addField('external_id', 'text', array(
                                       'name'          => 'external_id',
                                       'label'         => $this->_helper->__('External ID'),
                                       "maxlength"     => 100
                                   ));
    }
    protected function _addFieldClientNumber() {
        $this->_fieldset->addField('client_number', 'text', array(
                                       'name'          => 'client_number',
                                       'label'         => $this->_helper->__('Client number'),
                                       "maxlength"     => 100
                                   ));

    }

}
