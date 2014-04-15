<?php
/**
 * builder for settings fieldset
 */
class Zolago_Pos_Model_Form_Fieldset_Productreview extends Zolago_Common_Model_Form_Fieldset_Abstract {
    
    protected function _getHelper() {
        return Mage::helper('zolagopos');
    }
    protected function _addFieldProductStatus() {
        $this->_fieldset->addField('review_status', 'select', array(
                                       'name'          => 'review_status',
                                       'label'         => $this->_helper->__('Review Status'),
                                       'values'		   => Mage::getSingleton('udprod/source')->setPath('system_status')->toOptionArray(),
                                       'required'      => true,
                                   ));

    }
}