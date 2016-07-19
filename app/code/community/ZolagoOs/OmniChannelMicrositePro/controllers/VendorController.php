<?php

class ZolagoOs_OmniChannelMicrositePro_VendorController extends Mage_Core_Controller_Front_Action
{
    protected function _setTheme()
    {
        $theme = explode('/', Mage::getStoreConfig('zolagoos/vendor/interface_theme'));
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
    public function returnResult($result)
    {
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    public function checkEmailUniqueAction()
    {
        $email = Mage::app()->getRequest()->getParam('email');
        if (empty($email)) {
            return $this->returnResult(array(
                'error'=>true,
                'success'=>false,
                'message'=>'Empty email'
            ));
        } else {
            if (!Mage::helper('udmspro')->checkEmailUnique($email)) {
                return $this->returnResult(array(
                    'error'=>true,
                    'success'=>false,
                    'message'=>'Email is used'
                ));
            } else {
                return $this->returnResult(array(
                    'error'=>false,
                    'success'=>true,
                    'message'=>'Email is not used'
                ));
            }
        }
    }
    public function checkVendorNameUniqueAction()
    {
        $vendor_name = Mage::app()->getRequest()->getParam('vendor_name');
        if (empty($vendor_name)) {
            return $this->returnResult(array(
                'error'=>true,
                'success'=>false,
                'message'=>'Empty Shop Name'
            ));
        } else {
            if (!Mage::helper('udmspro')->checkVendorNameUnique($vendor_name)) {
                return $this->returnResult(array(
                    'error'=>true,
                    'success'=>false,
                    'message'=>'Shop Name is used'
                ));
            } else {
                return $this->returnResult(array(
                    'error'=>false,
                    'success'=>true,
                    'message'=>'Shop Name is not used'
                ));
            }
        }
    }
    protected function _getSession()
    {
        return Mage::getSingleton('udropship/session');
    }
    public function confirmAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('udropship/vendor/');
            return;
        }
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