<?php
/**
 * builder for stock fieldset
 */
class Zolago_Pos_Model_Form_Fieldset_Stock extends Zolago_Common_Model_Form_Fieldset_Abstract {
    
    protected function _getHelper() {
        return Mage::helper('zolagopos');
    }

    protected function _addFieldMinimalStock() {
        $this->_fieldset->addField('minimal_stock', 'text', array(
                                       'name'          => 'minimal_stock',
                                       'label'         => $this->_helper->__('Minimal stock'),
                                       'required'      => true,
                                       "class"         => "validate-digits form-control",
                                       "maxlength"     => 3
                                   ));
    }
    protected function _addFieldPriority() {
        $this->_fieldset->addField('priority', 'text', array(
                                       'name'          => 'priority',
                                       'label'         => $this->_helper->__('Priority'),
                                       'required'      => true,
                                       "class"         => "validate-digits form-control",
                                       "maxlength"     => 3
                                   ));
    }

}
