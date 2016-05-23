<?php
/**
  
 */

class ZolagoOs_OmniChannelMicrosite_VendorController extends Mage_Core_Controller_Front_Action
{
    protected $_loginFormChecked = false;

    protected function _setTheme()
    {
        $theme = explode('/', Mage::getStoreConfig('udropship/vendor/interface_theme'));
        if (empty($theme[0]) || empty($theme[1])) {
            $theme = 'default/default';
        }
        Mage::getDesign()->setPackageName($theme[0])->setTheme($theme[1]);
    }

    protected function _renderPage($handles=null, $active=null)
    {
        $this->_setTheme();
        $this->loadLayout($handles);
        if (($root = $this->getLayout()->getBlock('root'))) {
            $root->addBodyClass('udropship-vendor');
        }
        if ($active && ($head = $this->getLayout()->getBlock('header'))) {
            $head->setActivePage($active);
        }
        $this->_initLayoutMessages('udropship/session');
        $this->renderLayout();
    }

    public function registerAction()
    {
		/** @var Zolago_Common_Helper_Data $commonHlp */
		$commonHlp = Mage::helper("zolagocommon");
		if (!$commonHlp->useGalleryConfiguration()) {
			$this->_redirect('udropship/vendor/');
			return;
		}
		$this->_renderPage(null, 'register');
    }

    public function registerPostAction()
    {
		/** @var Zolago_Common_Helper_Data $commonHlp */
		$commonHlp = Mage::helper("zolagocommon");
		if (!$commonHlp->useGalleryConfiguration()) {
			$this->_redirect('udropship/vendor/');
			return;
		}
		
        $session = Mage::getSingleton('udropship/session');
        $hlp = Mage::helper('umicrosite');
        try {
            $data = $this->getRequest()->getParams();
            $session->setRegistrationFormData($data);
            $reg = Mage::getModel('umicrosite/registration')
                ->setData($data)
                ->validate()
                ->save();
            if (!Mage::getStoreConfig('udropship/microsite/auto_approve')) {
                $hlp->sendVendorSignupEmail($reg);
            }
            $hlp->sendVendorRegistration($reg);
            $session->unsRegistrationFormData();
            if (Mage::getStoreConfig('udropship/microsite/auto_approve')) {
                $vendor = $reg->toVendor();
                $vendor->setStatus(ZolagoOs_OmniChannel_Model_Source::VENDOR_STATUS_INACTIVE);
                if (Mage::getStoreConfig('udropship/microsite/auto_approve')==ZolagoOs_OmniChannelMicrosite_Model_Source::AUTO_APPROVE_YES_ACTIVE
                ) {
                    $vendor->setStatus(ZolagoOs_OmniChannel_Model_Source::VENDOR_STATUS_ACTIVE);
                }
                $_FILES = array();
                $vendor->save();
                Mage::getSingleton('udropship/session')->loginById($vendor->getId());
                if (!$this->_getVendorSession()->getBeforeAuthUrl()) {
                    $this->_getVendorSession()->setBeforeAuthUrl(Mage::getUrl('udropship'));
                }
            } else {
                $session->addSuccess($hlp->__('Thank you for application. As soon as your registration has been verified, you will receive an email confirmation'));
            }
        } catch (Exception $e) {
            $session->addError($e->getMessage());
            if ($this->getRequest()->getParam('quick')) {
                $this->_redirect('udropship/vendor/login');
            } else {
                $this->_redirect('*/*/register');
            }
            return;
        }
        $this->_loginPostRedirect();
    }

    public function registerSuccessAction()
    {
        $this->_renderPage(null, 'register');
    }

    protected function _loginPostRedirect()
    {
        $this->_getVendorSession()->loginPostRedirect($this);
    }
    protected function _getVendorSession()
    {
        return Mage::getSingleton('udropship/session');
    }

	protected function _getSession()
	{
		return Mage::getSingleton('udropship/session');
	}

	/**
	 * Action predispatch
	 *
	 * Check customer authentication for some actions
	 */
	public function preDispatch()
	{
		/***********************************************************************
		 *  Changning locale
		 ***********************************************************************/
		if(!$this->_getSession()->getLocale()) {
			$this->_getSession()->setLocale(Mage::app()->getLocale()->getLocaleCode());
		}
		if(!Mage::registry("dropship_switch_lang")) {
			Mage::register("dropship_switch_lang", 1);
		}
		// a brute-force protection here would be nice
		parent::preDispatch();
		$r = $this->getRequest();

		if (!$r->isDispatched()) {
			return;
		}
		$action = $r->getActionName();
		$session = Mage::getSingleton('udropship/session');
		if (Mage::helper('udropship')->isModuleActive('ZolagoOs_OmniChannelVendorPortalUrl')) {
			Mage::getConfig()->setNode('global/models/core/rewrite/url', 'ZolagoOs_OmniChannelVendorPortalUrl_Model_Url');
		} else {
			Mage::getConfig()->setNode('global/models/core/rewrite/url', 'ZolagoOs_OmniChannel_Model_Url');
		}

	}

	public function confirmAction()
	{
		if ($this->_getSession()->isLoggedIn()) {
			$this->_redirect('udropship/vendor/');
			return;
		}
		$this->_forward("accept", "vendor", "udropship");
		return;
		try {
			$id      = $this->getRequest()->getParam('id', false);
			$key     = $this->getRequest()->getParam('key', false);
			$backUrl = $this->getRequest()->getParam('back_url', false);
			if (empty($id) || empty($key)) {
				throw new Exception($this->__('Bad request.'));
			}
			try {

				$vendor = Mage::getModel('udropship/vendor')->load($id);
				if ((!$vendor) || (!$vendor->getId())) {
					throw new Exception('Failed to load vendor by id.');
				}
				if ($vendor->getConfirmation() !== $key) {
					throw new Exception($this->__('Wrong confirmation key.'));
				}

				// activate customer
				try {
					$vendor->setConfirmation(null);
					$password = Mage::helper('udmspro')->processRandomPattern('[AN*6]');
					$vendor->setPassword($password);
					$vendor->setPasswordEnc(Mage::helper('core')->encrypt($password));
					$vendor->setPasswordHash(Mage::helper('core')->getHash($password, 2));
					Mage::getResourceSingleton('udropship/helper')->updateModelFields($vendor, array('confirmation','password_hash','password_enc'));
				}
				catch (Exception $e) {
					throw new Exception($this->__('Failed to confirm vendor account.'));
				}

				Mage::helper('umicrosite')->sendVendorWelcomeEmail($vendor);
				$this->_getSession()->addSuccess("You've successfully confirmed your account. Please check your mailbox for email with your account information in order to login.");
				$this->_redirect('udropship/vendor/');
				return;
			}
			catch (Exception $e) {
				throw new Exception($this->__('Wrong vendor account specified.'));
			}
		}
		catch (Exception $e) {
			// die unhappy
			$this->_getSession()->addError($e->getMessage());
			$this->_redirect('udropship/vendor/');
			return;
		}
	}
}