<?php

require_once Mage::getModuleDir("controllers", "Mage_Customer") . DS . "AccountController.php";

class Zolago_Customer_AccountController extends Mage_Customer_AccountController
{
    protected $_wasLogged;

    /**
     * Override index
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages(array('catalog/session', 'udqa/session'));

        $this->getLayout()->getBlock('content')->append(
            $this->getLayout()->createBlock('customer/account_dashboard')
        );
        $this->getLayout()->getBlock('head')->setTitle($this->__('My Account'));
        $this->renderLayout();
    }
	
	/**
	 * Override logout - add message from cms block
	 */
	public function logoutAction() {
		// Do parent logout
		parent::logoutAction();

		// Generate cms block
		try{
			$cms = $this->getLayout()->
			createBlock("cms/block")->
			setBlockId("customer-logout-forget")->
			toHtml();
		}catch(Exception $e){
			$cms = $this->__("Log out success");
			Mage::logException($e);
		}
		Mage::getSingleton('core/session')->addSuccess($cms);		
		return $this->getResponse()->setRedirect('/?salt='.uniqid());
//		return $this->_redirect("/?salt=".uniqid());
	}

	/*
	 * Privacy setting action
	 */
    public function privacyAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        $block = $this->getLayout()->getBlock('notification-settings-general-subscription');
        if ($block) {
            $block->setRefererUrl($this->_getRefererUrl());
        }


        $this->getLayout()->getBlock('head')->setTitle($this->__('Account Information'));
        $this->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
        $this->renderLayout();
    }

    /**
     * Privacy setting action save
     */
    public function privacyPostAction()
    {
        if(!$this->getRequest()->isPost() || !$this->_validateFormKey()) {
            $this->_redirect('*/*/');
            return;
        }
        $session = Mage::getSingleton('customer/session');
        try {
            $value = (int)$this->getRequest()->getParam("forget_me");
            $customer = $session->getCustomer();
            $customer->setForgetMe($value);
            $resource = $customer->getResource();
            /* @var $resource Mage_Customer_Model_Resource_Customer */

            $resource->saveAttribute($customer, "forget_me");

            Mage::dispatchEvent("customer_privacy_changed", array(
                                    "customer"=>$customer
                                ));
        } catch (Mage_Core_Exception $e) {
            $session->addError($this->__($e->getMessage()));
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $session->addError($this->__("Some error occured!"));
            Mage::logException($e);
            return $this->_redirectReferer();
        }
        $session->addSuccess($this->__("Your privacy has been saved"));
        return $this->_redirectReferer();
    }


    /**
     * Override mesagge
     */
    public function loginPostAction() {

        $isPersistent = 1;//$this->_getSession()->getCustomer()->getRememeberMe();
        // Apply setting of persistance
        //$this->getRequest()->setPost("persistent_remember_me", $isPersistent);
        //$this->getRequest()->setParam("persistent_remember_me", $isPersistent);

        if(!$this->getRequest()->isPost()) {
            $this->_redirect('*/*/');
            return;
        }

        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/');
            return;
        }

        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        //trim username
        $login = $this->getRequest()->getPost('login');
        $login['username'] = trim($login['username']);
        $this->getRequest()->setPost('login', $login);

        parent::loginPostAction();



        // Add success if login sucessful (by core session - visable in both customer / checkout)
        if ($this->_getSession()->isLoggedIn()) {

            Mage::getSingleton('core/session')->addSuccess(
                Mage::helper("zolagocustomer")->__("You have been logged in")
            );

            if($this->getRequest()->getPost('redirect') === 'mypromotions') {
                $this->_redirect("mypromotions");
            }
            elseif(strpos($_SERVER['HTTP_REFERER'],'mypromotions') !== false) {
                $this->_redirectReferer();
            }
            elseif ($this->getRequest()->getParams('is_checkout') == 0) {
                $this->_redirect("customer/account");
            }

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
        if($this->getRequest()->getActionName()=="logout") {
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

            $postFirstname = $this->getRequest()->getParam('firstname');
            if($postFirstname !== null) {
                $postFirstname = trim($postFirstname);
            }

            $postLastname = $this->getRequest()->getParam('lastname');
            if($postLastname !== null) {
                $postLastname = trim($postLastname);
            }

            $postPhone = trim($this->getRequest()->getParam('phone'));
            $postErrors = false;

            if (($postFirstname === "" && $customer->getFirstname()) || ($postLastname === "" && $customer->getLastname())) {
                $this->_getSession()->addError(
                    Mage::helper("zolagocustomer")->__("You cannot remove your firstname and lastname")
                );
                $postErrors = true;
            }

            if($postPhone === "" && $customer->getPhone()) {
                $this->_getSession()->addError(
                    Mage::helper("zolagocustomer")->__("You cannot remove your phone")
                );
                $postErrors = true;
            }

            if($postErrors) {
                return $this->_redirect('*/*/edit');
            }

            if(empty($postEmail)) {
                $postEmail = $origEmail;
                $this->getRequest()->setParam("email", $origEmail);
            }

            if($origEmail==$postEmail) {
                parent::editPostAction();
                return $this->_redirectReferer();
            }

            if(!Zend_Validate::is($postEmail, 'EmailAddress')) {
                parent::editPostAction();
                return $this->_redirectReferer();
            }

            // Email validated & changed
            // Set orign email
            try {
                $this->getRequest()->setParam("email", $origEmail);
                $this->_registerEmailToken($customer, $postEmail);
                $this->_getSession()->addSuccess(
                    Mage::helper("zolagocustomer")
                    ->__("New email address will be set after confirmation. Check your new-email account. Change request expires after %s hours.",
                         Zolago_Customer_Model_Emailtoken::HOURS_EXPIRE)
                );
            } catch(Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError(Mage::helper("customer")
                                               ->__('Cannot save the customer.'));

                return $this->_redirectReferer();
            }
        }

        parent::editPostAction();
        return $this->_redirectReferer();
    }

    /**
     * Success Registration
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_Customer_AccountController
     */
    protected function _successProcessRegistration(Mage_Customer_Model_Customer $customer)
    {
        $session = $this->_getSession();
        if ($customer->isConfirmationRequired()) {
            /** @var $app Mage_Core_Model_App */
            $app = $this->_getApp();
            /** @var $store  Mage_Core_Model_Store*/
            $store = $app->getStore();
            $customer->sendNewAccountEmail(
                'confirmation',
                $session->getBeforeAuthUrl(),
                $store->getId()
            );
            $customerHelper = $this->_getHelper('customer');
            $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.',
                                           $customerHelper->getEmailConfirmationUrl($customer->getEmail())));
            $url = $this->_getUrl('*/*/index', array('_secure' => true));
        } else {
            $session->setCustomerAsLoggedIn($customer);
            $session->renewSession();
            $url = $this->_welcomeCustomer($customer);

            if (Mage::helper("zolagonewsletter")->isModuleActive()) {
                $model = Mage::getModel('zolagonewsletter/inviter');
                if ($customer->getData("is_subscribed") == 0) {
                    // send invitation mail, model takes care of handling everything
                    $model->sendInvitationEmail($customer->getEmail());

                }
            }

        }
        $this->_redirectSuccess($url);
        return $this;
    }

    public function editPassAction() {
        if (!$this->_validateFormKey()) {
            return $this->_redirectReferer();
        }

        if ($this->getRequest()->isPost()) {
            /** @var $customer Mage_Customer_Model_Customer */
            $customer = $this->_getSession()->getCustomer();

            /** @var $customerForm Mage_Customer_Model_Form */
            $customerForm = $this->_getModel('customer/form');
            $customerForm->setFormCode('customer_account_edit')
            ->setEntity($customer);

            $customerData = $customerForm->extractData($this->getRequest());
            $errors = array();
            $customerErrors = $customerForm->validateData($customerData);
            if ($customerErrors !== true) {
                $errors = array_merge($customerErrors, $errors);
            } else {
                $customerForm->compactData($customerData);
                $errors = array();
                if ($this->getRequest()->getParam('change_password')) {
                    $newPass = $this->getRequest()->getPost('password');
                    $confPass = $this->getRequest()->getPost('confirmation');

                    if (strlen($newPass)) {
                        /**
                         * Set entered password and its confirmation - they
                         * will be validated later to match each other and be of right length
                         */
                        $customer->setPassword($newPass);
                        $customer->setConfirmation($confPass);
                    } else {
                        $errors[] = $this->__('New password field cannot be empty.');
                    }

                    // Validate account and compose list of errors if any
                    $customerErrors = $customer->validate();
                    if (is_array($customerErrors)) {
                        $errors = array_merge($errors, $customerErrors);
                    }
                }
            }
            if (!empty($errors)) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
                foreach ($errors as $message) {
                    $this->_getSession()->addError($message);
                }
                return $this->_redirectReferer();
            }
            try {
                $customer->setConfirmation(null);
                $customer->save();
                $this->_getSession()->setCustomer($customer)
                ->addSuccess($this->__('The account information has been saved.'));

                $this->_redirectReferer();
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                ->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                ->addException($e, $this->__('Cannot save the customer.'));
            }
        }
        return $this->_redirectReferer();
    }

    /**
     * Forgot customer password action
     */
    public function forgotPasswordPostAction()
    {
        $email = (string) $this->getRequest()->getPost('email');
        $email = trim($email);
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

    /**
     * Display reset forgotten password form
     *
     * User is redirected on this action when he clicks on the corresponding link in password reset confirmation email
     *
     */
    public function resetPasswordAction()
    {
        $resetPasswordLinkToken = (string) $this->getRequest()->getQuery('token');
        $customerId = (int) $this->getRequest()->getQuery('id');
        try {
            $this->_validateResetPasswordLinkToken($customerId, $resetPasswordLinkToken);
            $this->loadLayout();
            // Pass received parameters to the reset forgotten password form
            $customer = $this->_getModel("customer/customer")->load($customerId);
            $this->getLayout()->getBlock('resetPassword')
            ->setCustomerId($customerId)
            ->setResetPasswordLinkToken($resetPasswordLinkToken)
            ->setEmail($customer->getEmail());

            // autologin
            $this->_saveRestorePasswordParameters($customerId,$resetPasswordLinkToken);
            $this->_getSession()->setCustomerAsLoggedIn($customer);

            $this->renderLayout();
        } catch (Exception $exception) {
            $this->_getSession()->getMessages(true);
            $this->_getSession()->addError( $this->_getHelper('customer')->__('Your password reset link has expired.'));
            $this->_redirect('*/*/forgotpassword');
        }
    }

    protected function _registerEmailToken(
        Mage_Customer_Model_Customer $customer, $newEmail
    ) {
        // Save Model
        $model = Mage::getModel("zolagocustomer/emailtoken");
        $model->setData(array(
                            "customer_id" => $customer->getId(),
                            "token" => Mage::helper("zolagocustomer")->generateToken(),
                            "new_email" => $newEmail
                        ));
        $model->save();

        if(!$model->sendMessage()) {
            throw new Exception(Mage::helper("customer")
                                ->__('Cannot send email'));
        }

    }

    /**
     * Handle checkout context login
     * @return type
     */
    protected function _loginPostRedirect() {
        if($this->getRequest()->getParam("is_checkout")) {
            if($this->_getSession()->isLoggedIn()) {
                $this->_getSession()->setBeforeAuthUrl(Mage::getUrl("checkout/singlepage/index"));
            } else {
                $this->_getSession()->setBeforeAuthUrl(Mage::getUrl("checkout/guest/login"));
            }
        }
        return parent::_loginPostRedirect();
    }

    public function passwordAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        $block = $this->getLayout()->getBlock('customer_edit');
        if ($block) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $data = $this->_getSession()->getCustomerFormData(true);
        $customer = $this->_getSession()->getCustomer();
        if (!empty($data)) {
            $customer->addData($data);
        }
        if ($this->getRequest()->getParam('changepass') == 1) {
            $customer->setChangePassword(1);
        }

        $this->getLayout()->getBlock('head')->setTitle($this->__('Account Information'));
        $this->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
        $this->renderLayout();
    }

    /**
     * Create customer account action
     */
    public function createPostAction()
    {
        /** @var $session Mage_Customer_Model_Session */
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session->setEscapeMessages(true); // prevent XSS injection in user input
        if (!$this->getRequest()->isPost()) {
            $errUrl = $this->_getUrl('*/*/create', array('_secure' => true));
            $this->_redirectError($errUrl);
            return;
        }

        $customer = $this->_getCustomer();
        $data = $this->getRequest()->getPost();
        if(isset($data['email'])) {
            $data['email'] = trim($data['email']);
        }
        try {
            $errors = $this->_getCustomerErrors($data);
            if (empty($errors)) {
                unset($data['agreement']);
                $data['is_subscribed'] = isset($data['is_subscribed']) ? 1 : 0;
                $customer->setData($data);
                /* needed for proper newsletter handling */
                /* needed for proper salesmanago cart sync */
                $customer->setIsJustRegistered(true);
                $customer->save();
                $this->_dispatchRegisterSuccess($customer);
                $this->_successProcessRegistration($customer);

                if($data['is_subscribed']) { //new customer, must confirm newsletter (OR NOT!!!!)
                    $status = Mage::getModel('newsletter/subscriber')->loadByEmail($data['email'])->getStatus();
                    if ($status == Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED) {
                        if($this->getRequest()->getParam('referrer') == 'about') {
                            //register made from about us site
                            $msg = "Thank you for subscribing to our mailing list. In order to get our newsletter and receive coupon codes you have to confirm your e-mail.<br />Confirm your e-mail by clicking link in message that we have just sent to you.<br />Newsletter setting in your account will be changed after e-mail confirmation. You will also get your coupon codes.";
                        } else {
                            //normal register
                            $msg = "Your subscribtion has been saved.<br />To start receiving our newsletter you have to confirm your e-mail by clicking confirmation link in e-mail that we have just sent to you.<br />Newsletter setting in your account will be changed after e-mail confirmation.";
                        }
                    }  else {
                        $msg = "Thank you for your subscription.";
                    }
                    $session->addSuccess(Mage::helper('zolagonewsletter')->__($msg));
                }

                if(strpos($this->_getRefererUrl(),'mypromotions') != -1) {
                    $this->_redirectReferer();
                }
                elseif($this->getRequest()->getParam('redirect') == 'mypromotions') {
                    $this->_redirect('mypromotions');
                }
                return;
            } else {
                $this->_addSessionError($errors);
            }
        } catch (Mage_Core_Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost());
            if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                $url = $this->_getUrl('customer/account/forgotpassword');
                $message = $this->__("There is already an account with this email address. If you are sure that it is your email address, <a href='%s'>click here</a> to get your password and access your account.", $url);
                $session->setEscapeMessages(false);
            } else {
                $message = $e->getMessage();
            }
            $session->addError($message);
        } catch (Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost())
            ->addException($e, $this->__('Cannot save the customer.'));
        }
        if(strpos($this->_getRefererUrl(),'mypromotions') != -1 || $this->getRequest()->getParam('redirect') == 'mypromotions') {
            $this->_redirectReferer();
        } else {
            $errUrl = $this->_getUrl('*/*/create', array('_secure' => true));
            $this->_redirectError($errUrl);
        }
    }

    /**
     * Validate customer data and return errors if they are
     *
     * @param array $customer
     * @return array|string
     */
    protected function _getCustomerErrors($customer)
    {
        /**
         * $required
         *      key - field name
         *      value - field length (0 means not needed)
         */
        $required = array(
                        "email"         => "1",
                        "password"      => "6",
                        "agreement"     => "1",
                        "is_subscribed" => "0"
                    );
        $errors = array();
        foreach($customer as $field => $value) {
            if(!isset($required[$field])) {
                $errors = array();
                $errors[] = $this->__("Some error occured");
                return $errors;
            } else {
                if(strlen($value) < $required[$field]) {
                    $errors[] = $this->__(ucfirst($field)." is not correct");
                }
                elseif($field == 'email' && !Zend_Validate::is($value, 'EmailAddress')) {
                    $errors[] = $this->__("Provided email is not correct");
                }
            }
        }
        return $errors;
    }

}
