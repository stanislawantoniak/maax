<?php

require_once Mage::getModuleDir("controllers", "Mage_Customer") . DS . "AccountController.php";

class Zolago_Customer_AccountController extends Mage_Customer_AccountController
{
    public function editPostAction() {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/edit');
        }

        if ($this->getRequest()->isPost()) {
            /** @var $customer Mage_Customer_Model_Customer */
            $customer = $this->_getSession()->getCustomer();
            $origEmail = trim($customer->getEmail());
            $postEmail = trim($this->getRequest()->getParam('email'));
            
            if(empty($postEmail)){
                $postEmail = $origEmail;
                $this->getRequest()->setParam("email", $origEmail);
            }
            
            if($origEmail==$postEmail){
                return parent::editPostAction();
            }
            
            if(!Zend_Validate::is($postEmail, 'EmailAddress')){
                return parent::editPostAction();
            }
            
            // Email validated & changed
            // Set orign email
            try{
                $this->getRequest()->setParam("email", $origEmail);
                $this->_registerEmailToken($customer, $postEmail);
                $this->_getSession()->addSuccess(
                        Mage::helper("zolagocustomer")
                            ->__("New email address will be set after confirmation. Check your new-email account. Change request expires after %s hours.", 
                                Zolago_Customer_Model_Emailtoken::HOURS_EXPIRE)
                );
            }catch(Exception $e){
                Mage::logException($e);
                $this->_getSession()->addError(Mage::helper("customer")
                    ->__('Cannot save the customer.'));
                    
                return $this->_redirectReferer();
            }
        }
        
        return parent::editPostAction();
    }
    
    protected function _registerEmailToken(
        Mage_Customer_Model_Customer $customer, $newEmail
    ){
        // Save Model
        $model = Mage::getModel("zolagocustomer/emailtoken");
        $model->setData(array(
            "customer_id" => $customer->getId(),
            "token" => Mage::helper("zolagocustomer")->generateToken(),
            "new_email" => $newEmail
        ));
        $model->save();
        
        if(!$model->sendMessage()){
            throw new Exception(Mage::helper("customer")
                ->__('Cannot send email'));
        }
        
    }
   
}
