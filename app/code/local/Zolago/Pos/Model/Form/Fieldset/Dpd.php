<?php
/**
 * builder for settings fieldset
 */
class Zolago_Pos_Model_Form_Fieldset_Dpd extends Zolago_Common_Model_Form_Fieldset_Abstract {
    
    protected function _getHelper() {
        return Mage::helper('zolagopos');
    }
    protected function _addFieldUseDpd() {
        $this->_fieldset->addField('use_zolagodpd', 'select', array(
                                       'name'          => 'use_zolagodpd',
                                       'label'         => $this->_helper->__("Use this DPD setting"),
                                       'values'		   => Mage::getSingleton("adminhtml/system_config_source_yesno")->toOptionArray(),
                                       'required'      => false,
									   'class'		   => "form-control"
                                   ));

}
