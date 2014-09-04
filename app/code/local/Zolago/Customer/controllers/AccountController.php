<?php

require_once Mage::getModuleDir("controllers", "Mage_Customer") . DS . "AccountController.php";

class Zolago_Customer_AccountController extends Mage_Customer_AccountController
{
	protected $_wasLogged;

	/**
	 * Override mesagge
	 */
	public function loginPostAction() {
		parent::loginPostAction();
		// Add success if login sucessful (by core session - visable in both customer / checkout)
		if($this->_getSession()->isLoggedIn()){
			Mage::getSingleton('core/session')->addSuccess(
				Mage::helper("zolagocustomer")->__("You have been logged in")
			);
		}
	}

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        // a brute-force protection here would be nice

        parent::preDispatch();

        $action = $this->getRequest()->getActionName();

        if (preg_match("/^(forgotpasswordmessage)/i", $action)) {
            if ($this->getFlag($action, "no-dispatch")) {
                unset($this->_flags[$action]['no-dispatch']);
                $this->getResponse()->clearHeader('Location');
                $this->getResponse()->setHttpResponseCode(200);
            }
        }

        // Skip logout error - @httpdocs changes
        if($this->getRequest()->getActionName()=="logout"){
            $this->setFlag('', 'no-dispatch', false);
        }

        if (!$this->getRequest()->isDispatched()) {
            return;
        }
    }
	
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

    /**
     * Forgot customer password action
     */
    public function forgotPasswordPostAction()
    {
        $email = (string) $this->getRequest()->getPost('email');
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                $this->_getSession()->addError($this->__('Invalid email address.'));
                $this->_redirect('*/*/forgotpassword');
                return;
            }

            /** @var $customer Mage_Customer_Model_Customer */
            $customer = $this->_getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

            if ($customer->getId()) {
                try {
                    $newResetPasswordLinkToken =  $this->_getHelper('customer')->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    $customer->sendPasswordResetConfirmationEmail();
                } catch (Exception $exception) {
                    $this->_getSession()->addError($exception->getMessage());
                    $this->_redirect('*/*/forgotpassword');
                    return;
                }
            }
            $this->_getSession()->setData("forgotpassword_customer_email", $this->_getHelper("customer")
                ->escapeHtml($email));
            $this->_redirect('*/*/forgotpasswordmessage');
            return;
        } else {
            $this->_getSession()->addError($this->__('Please enter your email.'));
            $this->_redirect('*/*/forgotpassword');
            return;
        }
    }

    public function forgotPasswordMessageAction()
    {
        $this->loadLayout();
        $this->renderLayout();
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
	
	/**
	 * Handle checkout context login
	 * @return type
	 */
	protected function _loginPostRedirect() {
		if($this->_getSession()->isLoggedIn() && $this->getRequest()->getParam("is_checkout")){
			$this->_getSession()->setBeforeAuthUrl(Mage::getUrl("checkout/onepage/index"));
		}
		return parent::_loginPostRedirect();
	}
   
}
