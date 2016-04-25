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
        $this->_renderPage(null, 'register');
    }

    public function registerPostAction()
    {
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
}