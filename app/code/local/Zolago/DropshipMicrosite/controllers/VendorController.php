<?php

require_once Mage::getModuleDir('controllers', "Unirgy_DropshipMicrosite") . DS . "VendorController.php";

class Zolago_DropshipMicrosite_VendorController extends Unirgy_DropshipMicrosite_VendorController
{

    public function preDispatch()
    {
        // a brute-force protection here would be nice
        parent::preDispatch();

        $r = $this->getRequest();

        if (!$r->isDispatched()) {
            return;
        }
        $action = $r->getActionName();
        $session = Mage::getSingleton('udropship/session');

        if (!$session->isLoggedIn() && !Mage::registry('udropship_login_checked')) {
            Mage::register('udropship_login_checked', true);
            if ($r->getPost('login')) {
                $login = $this->getRequest()->getPost('login');
                if (!empty($login['username']) && !empty($login['password'])) {
                    try {
                        if (!$session->login($login['username'], $login['password'])) {
                            $session->addError($this->__('Invalid username or password.'));
                        }
                        $session->setUsername($login['username']);
                    }
                    catch (Exception $e) {
                        $session->addError($e->getMessage());
                    }
                } else {
                    $session->addError($this->__('Login and password are required'));
                }
                if ($session->isLoggedIn()) {
                    $this->_loginPostRedirect();
                }
            }
            if (!preg_match('#^(login|logout|password)#i', $action)) {
                $this->_forward('login', 'vendor', 'udropship');
            }
        } else {
            if (Mage::helper('udropship')->isModuleActive('Unirgy_DropshipVendorPortalUrl')) {
                Mage::getConfig()->setNode('global/models/core/rewrite/url', 'Unirgy_DropshipVendorPortalUrl_Model_Url');
            } else {
                Mage::getConfig()->setNode('global/models/core/rewrite/url', 'Unirgy_Dropship_Model_Url');
            }
        }

    }


}