<?php
class Orba_Informwhenavailable_Model_Config extends Mage_Core_Model_Config {
    
    const INFORMWHENAVAILABLE_DEFAULT_EMAIL_TEMPLATE = 'inform_when_available';
    
    public function isActive() {
        return (bool)Mage::getStoreConfig('informwhenavailable/config/active');
    }
    
    public function getSenderName($store_id) {
        $sender_name = Mage::getStoreConfig('informwhenavailable/config/sender_name', $store_id);
        if (!$sender_name) {
            $sender_name = Mage::getStoreConfig('trans_email/ident_general/name', $store_id);
        }
        return $sender_name;
    }
    
    public function getSenderEmail($store_id) {
        $sender_email = Mage::getStoreConfig('informwhenavailable/config/sender_email', $store_id);
        if (!$sender_email) {
            $sender_email = Mage::getStoreConfig('trans_email/ident_general/email', $store_id);
        }
        return $sender_email;
    }
    
    public function getEmailSubject($store_id) {
        return Mage::getStoreConfig('informwhenavailable/config/email_subject', $store_id);
    }
    
    public function getEmailTemplate($store_id) {
        $template_id = Mage::getStoreConfig('informwhenavailable/config/email_template', $store_id);
        if ($template_id) {
            return Mage::getModel('informwhenavailable/template')->getCodeById($template_id);
        }
        return null;
    }
    
}