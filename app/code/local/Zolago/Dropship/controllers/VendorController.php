<?php

require_once Mage::getModuleDir('controllers', "ZolagoOs_OmniChannel") . DS . "VendorController.php";

class Zolago_Dropship_VendorController extends ZolagoOs_OmniChannel_VendorController {
	/**
	 * Index
	 */
	public function indexAction() {
		return $this->_forward('dashboard');
		/*
		if (Mage::helper('udropship')->isUdpoActive()) {
			$session = $this->_getSession();
			if($session->isOperatorMode()){
				$operator = $session->getOperator();
				if($operator->isAllowed("udpo/vendor")){
					return parent::indexAction();
				}else{
					return $this->_forward('dashboard');
				}
			}
		}
		return parent::indexAction();
		*/
	}

    public function savePasswordAction() {
        /** @var Zolago_Dropship_Model_Session $session */
        $session = Mage::getSingleton('udropship/session');
        $request = $this->getRequest();
        $param = $request->getPost();

        if ($request->isPost()) {
            try {
                /** @var Zolago_Dropship_Model_Vendor $vendor */
                $vendor = $session->getVendor();
                /** @var Zolago_Operator_Model_Operator $operator */
                $operator = $session->getOperator();
                if ($operator->getId()) {
                    $operator->setPostPassword($param['password']);
                    $operator->save();
                } elseif ($vendor->getId()) {
                    $vendor->setData('password',$param['password']);
                    $tmp = array('password' => $param['password']);
                    Mage::dispatchEvent('udropship_vendor_preferences_save_before',
                        array('vendor' => $vendor, 'post_data' => &$tmp)
                    );
                    $vendor->save();
                               
                }                
                $session->addSuccess(Mage::helper('udropship')->__('Password has been saved'));
            } catch (Exception $e) {
                Mage::logException($e);
                $session->addError($e->getMessage());
            }
        }
        $this->_redirect(empty($param['redirectAfter'])? "udropship/vendor/editPassword":$param['redirectAfter']);        
    }

	public function passwordPostAction()
	{

		$session = Mage::getSingleton('udropship/session');
		$hlp = Mage::helper('udropship');
		try {
			$r = $this->getRequest();
			if (($confirm = $r->getParam('confirm'))) {
				$password = $r->getParam('password');
				$passwordConfirm = $r->getParam('password_confirm');
				$vendor = Mage::getModel('udropship/vendor')->load($confirm, 'random_hash');

				if (!$password || !$passwordConfirm || $password!=$passwordConfirm || !$vendor->getId()) {
					$session->addError('Invalid form data');
					$this->_redirect('*/*/password', array('confirm'=>$confirm));
					return;
				}

				//only active vendor can reset password
				if($vendor->getStatus() == ZolagoOs_OmniChannel_Model_Source::VENDOR_STATUS_ACTIVE){
					$vendor->setPassword($password)->unsRandomHash()->save();
					$session->loginById($vendor->getId());
					$session->addSuccess($hlp->__('Your password has been reset.'));
					$this->_redirect('*/*');
				} else {
					$session->addError($hlp->__('Your vendor account is not active.'));
					$this->_redirect('*/*/login');
					return;
				}

			} elseif (($email = $r->getParam('email'))) {
				$vendor = Mage::getModel('zolagodropship/vendor')->load($email, 'email');
				//only active vendor can reset password
				if($vendor->getStatus() == ZolagoOs_OmniChannel_Model_Source::VENDOR_STATUS_ACTIVE){
					$hlp->sendPasswordResetEmail($email);
					$session->addSuccess($hlp->__('Thank you, password reset instructions have been sent to the email you have provided, if a vendor with such email exists.'));
					$this->_redirect('*/*/login');
				} else {
					$session->addError($hlp->__('Your vendor account is not active.'));
					$this->_redirect('*/*/login');
					return;
				}
			} else {
				$session->addError($hlp->__('Invalid form data'));
				$this->_redirect('*/*/password');
			}
		} catch (Exception $e) {
			$session->addError($e->getMessage());
			$this->_redirect('*/*/password');
		}
	}
	/**
	 * Dasboard - move index if possible
	 */
	public function dashboardAction(){
        if (Mage::helper('udropship')->isUdpoActive() ) {
			$session = $this->_getSession();
			if($session->isOperatorMode()){
				$operator = $session->getOperator();
				if($operator->isAllowed("udpo/vendor")){
					return $this->_forward('index', 'vendor', 'udpo');
				}
			}
        }
		$this->_renderPage(null, "dashboard");
	}
	
	/**
	 * Denied action
	 */
	public function deniedAction(){
		die("Access denied");
	}
	
	/**
	 * Set locale
	 */
	public function setLocaleAction(){
		$locale = $this->getRequest()->getParam("locale", 
				Mage::app()->getLocale()->getLocaleCode());
		$this->_getSession()->setLocale($locale);
		return $this->_redirectReferer();
	}

    public function editPasswordAction() {
        $this->_renderPage(null, 'editpassword');
    }
    public function preDispatch() {
	    $registryKey = 'redirect_login_url';
        if (!Mage::registry($registryKey)) {
            $url = Mage::helper('core/url')->getCurrentUrl();
            $redirect = $this->getRequest()->getParam('redirectUrl',$url);
	        if($redirect) {
		        Mage::unregister($registryKey);
		        Mage::register($registryKey, $redirect, true);
	        }
        }

        return parent::preDispatch();
    }
}


