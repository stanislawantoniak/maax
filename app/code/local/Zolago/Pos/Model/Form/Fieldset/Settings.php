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
                                       "maxlength"     => 100
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

}
