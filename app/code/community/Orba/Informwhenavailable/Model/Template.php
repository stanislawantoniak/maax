<?php
class Orba_Informwhenavailable_Model_Template extends Mage_Core_Model_Resource_Email_Template_Collection {
    
    public function toOptionArray() {
        return array_merge(array('0' => ''), $this->_toOptionArray('template_id', 'template_code'));
    }
    
    public function getCodeById($id) {
        $template = Mage::getModel('core/email_template')->load($id);
        return $template->getTemplateCode();
    }
    
}