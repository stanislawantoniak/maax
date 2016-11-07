<?php
class Zolago_Customer_Model_Emailtoken extends Mage_Core_Model_Abstract{
    
    const EMAIL_TEMPLATE = "zolagocustomer_confirmemail"; 
    const XML_PATH_CONFIRM_CHANGE_EMAIL = 'customer/password/zolagocustomer_confirmemail';
    const CONFIRM_PATH = "zolagocustomer/confirm/confirm";
    const HOURS_EXPIRE = 24;
    
    protected function _construct() {
        $this->_init('zolagocustomer/emailtoken');
    }
    
    /**
     * @return boolean
     */
    public function sendMessage() {
        if(!($this->getId() && $this->getNewEmail() 
            && $this->getToken() && $this->getCustomerId())){
            return;
        }
        
        $customer = Mage::getModel("customer/customer")
            ->load($this->getCustomerId());
        /* @var $customer Mage_Customer_Model_Customer */
        
        if(!$customer->getId()){
            return false;
        }
        
        $store = $customer->getStore();
        
        $templateParams = array(
            "customer" => $customer,
            "new_email" => $this->getNewEmail(),
            "store" => $store,
            "confirm_link" => $this->getConfirmLink()
            
        );
        
        return $this->_sendEmailTemplate($customer, 
            self::XML_PATH_CONFIRM_CHANGE_EMAIL, $templateParams, $store->getId());
    }
    
    public function getConfirmLink($token=null) {
        if($token===null){
            $token = $this->getToken();
        }
        return Mage::getUrl(self::CONFIRM_PATH, array("token"=>$token));
    }

    protected function _sendEmailTemplate($customer, 
        $templateKey, $templateParams = array(), $storeId = null)
    {
        $templateParams['use_attachments'] = true;

        /* @var $mailer Zolago_Common_Model_Core_Email_Template_Mailer */
        $mailer = Mage::getModel('core/email_template_mailer');
        $mailer->setTemplateParams($templateParams);
        $templateParams = $mailer->getTemplateParams();

        $emailTemplate = Mage::getModel("core/email_template");
        /* @var $emailTempalte Mage_Core_Model_Email_Template */

        
        // Set required design parameters 
        // and delegate email sending to Mage_Core_Model_Email_Template
        $emailTemplate->
            setDesignConfig(array('area' => 'frontend', 'store' => $storeId));
        
        $template = Mage::getStoreConfig($templateKey,$storeId);
        if (is_numeric($template)) {
            $emailTemplate->load($template);
        } else {
            $localeCode = Mage::getStoreConfig('general/locale/code', $storeId);
            $emailTemplate->loadDefault($template, $localeCode);
        }

        $senderName = Mage::getStoreConfig('trans_email/ident_support/name',
                                                                    $storeId);
        $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email', 
                                                                    $storeId);
        
        $emailTemplate->setSenderEmail($senderEmail);
        $emailTemplate->setSenderName($senderName);
        
        if(!$emailTemplate->getTemplateSubject()){
            $emailTemplate->setTemplateSubject(Mage::helper("zolagocustomer")
                ->__("Confirm new Email address"));
        }
        
        return $emailTemplate->send(
            $this->getNewEmail(), 
            $customer->getName(),
            $templateParams
        );
            
    }
}
