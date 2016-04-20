<?php

require_once Mage::getModuleDir('controllers', "ZolagoOs_OmniChannelMicrosite") . DS . "VendorController.php";

class Zolago_DropshipMicrosite_VendorController extends ZolagoOs_OmniChannelMicrosite_VendorController
{
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