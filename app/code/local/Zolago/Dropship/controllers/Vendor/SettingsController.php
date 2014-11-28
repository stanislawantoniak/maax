<?php

require_once Mage::getModuleDir('controllers', "Zolago_Dropship") . DS . "VendorController.php";

class Zolago_Dropship_Vendor_SettingsController extends Zolago_Dropship_VendorController
{

    public function infoAction()
    {
        if (Mage::helper('udropship')->isUdpoActive()) {
            $session = $this->_getSession();
            if ($session->isOperatorMode()) {
                $operator = $session->getOperator();
                if ($operator->isAllowed("udpo/vendor")) {
                    return $this->_forward('index', 'vendor', 'udpo');
                }
            }
        }
        $this->_renderPage(null, "info");
    }

    public function shippingAction()
    {
        if (Mage::helper('udropship')->isUdpoActive()) {
            $session = $this->_getSession();
            if ($session->isOperatorMode()) {
                $operator = $session->getOperator();
                if ($operator->isAllowed("udpo/vendor")) {
                    return $this->_forward('index', 'vendor', 'udpo');
                }
            }
        }
        $this->_renderPage(null, "shipping");
    }

    public function rmaAction()
    {
        if (Mage::helper('udropship')->isUdpoActive()) {
            $session = $this->_getSession();
            if ($session->isOperatorMode()) {
                $operator = $session->getOperator();
                if ($operator->isAllowed("udpo/vendor")) {
                    return $this->_forward('index', 'vendor', 'udpo');
                }
            }
        }
        $this->_renderPage(null, "rma");
    }


    /**
     * Save USTAWIENIA PODSTAWOWE
     */
    public function infoPostAction()
    {
        $session = Mage::getSingleton('udropship/session');

        $r = $this->getRequest();

        if ($r->isPost()) {
            $p = $r->getPost();


            try {
                $v = $session->getVendor();

                $vendorId = $v->getVendorId();

                $vendorPreferences = Mage::getModel('zolagodropship/preferences');
                $vendorPreferences->load($vendorId, 'vendor_id');

                $data = array('vendor_id' => $vendorId);
                $data = array_merge($data, $p);
                $vendorPreferences->addData($data);
                $vendorPreferences->save();

                foreach (
                    array(
                        'email', 'password',
                        'billing_email', 'billing_street',
                        'billing_city',
                        'billing_zip'
                    ) as $f) {
                    if (array_key_exists($f, $p)) {
                        $v->setData($f, $p[$f]);
                    }
                }

                Mage::dispatchEvent('udropship_vendor_preferences_save_before',
                    array('vendor' => $v, 'post_data' => &$p)
                );
                $v->save();


                $session->addSuccess($this->__('Settings has been saved'));
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        }
        $this->_redirect('udropship/vendor_settings/info');
    }


    /**
     * SPOSOBY DOSTAWY
     */
    public function shippingPostAction()
    {
        $session = Mage::getSingleton('udropship/session');

        $r = $this->getRequest();

        if ($r->isPost()) {
            $p = $r->getPost();

            try {
                $v = $session->getVendor();

                foreach (
                    array(
                        'use_dhl', 'dhl_account','dhl_login', 'dhl_password','dhl_ecas','dhl_terminal',
                        'use_orbaups', 'orbaups_account','orbaups_login', 'orbaups_password'
                    ) as $f) {
                    if (array_key_exists($f, $p)) {
                        $v->setData($f, $p[$f]);
                    }
                }

                Mage::dispatchEvent('udropship_vendor_preferences_save_before',
                    array('vendor' => $v, 'post_data' => &$p)
                );
                $v->save();


                $session->addSuccess($this->__('Settings has been saved'));
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        }
        $this->_redirect('udropship/vendor_settings/shipping');
    }
    public function rmaPostAction()
    {
        $session = Mage::getSingleton('udropship/session');

        $r = $this->getRequest();

        if ($r->isPost()) {
            $p = $r->getPost();


            try {
                $v = $session->getVendor();

                $vendorId = $v->getVendorId();

                $vendorPreferences = Mage::getModel('zolagodropship/preferences');
                $vendorPreferences->load($vendorId, 'vendor_id');

                $data = array('vendor_id' => $vendorId);
                $data = array_merge($data, $p);
                $vendorPreferences->addData($data);
                $vendorPreferences->save();

                foreach (
                    array(
                        'dhl_rma', 'dhl_rma_account','dhl_rma_login', 'dhl_rma_password',
                        'orbaups_rma', 'orbaups_rma_account','orbaups_rma_login', 'orbaups_rma_password'
                    ) as $f) {
                    if (array_key_exists($f, $p)) {
                        $v->setData($f, $p[$f]);
                    }
                }

                Mage::dispatchEvent('udropship_vendor_preferences_save_before',
                    array('vendor' => $v, 'post_data' => &$p)
                );
                $v->save();


                $session->addSuccess($this->__('Settings has been saved'));
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        }
        $this->_redirect('udropship/vendor_settings/rma');
    }
}


