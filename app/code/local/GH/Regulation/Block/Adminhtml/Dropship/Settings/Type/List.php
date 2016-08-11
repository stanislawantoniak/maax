<?php
/**
 * table with connected types
 */
class GH_Regulation_Block_Adminhtml_Dropship_Settings_Type_List extends
    Varien_Data_Form_Element_Abstract {

    public function getHtml() {
        $this->_renderer = Mage::getSingleton('core/layout')->createBlock('ghregulation/adminhtml_dropship_renderer_list_type');
        $this->_renderer->setData('createUrl',$this->getData('createUrl'));
        $this->_renderer->setData('vendor_id',$this->getData('vendor_id'));
        $this->_renderer->setData('regulation_kind_id',$this->getData('regulation_kind_id'));
        return parent::getHtml();
    }



}