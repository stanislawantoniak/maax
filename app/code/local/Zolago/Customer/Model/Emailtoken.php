<?php
class Zolago_Customer_Model_Emailtoken extends Mage_Core_Model_Abstract{
    protected function _construct() {
        $this->_init('zolagocustomer/emailtoken');
    }
    
    /**
     * @return boolean
     */
    public function sendMessage() {
        if(!($this->getId() && $this->getNewEmail() && $this->getToken() && $this->getCustomerId())){
            return;
        }
        
        $customer = Mage::getModel("customer/customer")->load($this->getCustomerId());
        /* @var $customer Mage_Customer_Model_Customer */
        
        if(!$customer->getId()){
            return false;
        }
        
        $variables = array(
            "customer" => Mage::getModel("customer/customer")->load($this->getCustomerId()),
            "new_email" => $this->getNewEmail(),
            "store" => $customer->getStore(),
            
        );
        
        $emailModel = Mage::getModel("core/email_template");
        /* @var $emailModel Mage_Core_Model_Email_Template */
        $filter = $emailModel->getTemplateFilter();
        /* @var $filter Mage_Core_Model_Email_Template_Filter */
        $filter->setVariables($variables);
        $filter->setStoreId($customer->getStoreId());
        
        $emailModel->setTemplateCode("zolagocustomer_confirmemail");
        return $emailModel->send($this->getNewEmail());
    }
}

?>
