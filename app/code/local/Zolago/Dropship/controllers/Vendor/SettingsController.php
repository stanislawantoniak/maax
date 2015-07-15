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
        /** @var Zolago_Dropship_Model_Session $session */
        $session = Mage::getSingleton('udropship/session');
        $request = $this->getRequest();
        $param = $request->getPost();

        if ($request->isPost()) {
	        $errorData = array();
            try {
                /** @var Zolago_Dropship_Model_Vendor $vendor */
                $vendor = $session->getVendor();
                /** @var Zolago_Operator_Model_Operator $operator */
                $operator = $session->getOperator();

                if ($vendor->getId() && !$operator->getId()) {
                    foreach (
                        array(
                            'company_name',
                            'tax_no',
                            'www',
                            'contact_email',
                            'contact_telephone',
                            'executive_firstname',
                            'executive_lastname',
                            'executive_telephone',
                            'executive_telephone_mobile',
                            'email',
                            'password',
                            'billing_email',
                            'billing_street',
                            'billing_city',
                            'billing_zip',
                            'administrator_firstname',
                            'administrator_lastname',
                            'administrator_telephone',
                            'administrator_telephone_mobile'
                        ) as $key) {
                        if (array_key_exists($key, $param) && $vendor->getData($key) != $param[$key]) {
							$errorData[$key] = $param[$key];
                            $vendor->setData($key, $param[$key]);
                        }
                    }

                    Mage::dispatchEvent('udropship_vendor_preferences_save_before',
                        array('vendor' => $vendor, 'post_data' => &$param)
                    );
                    $vendor->save();

                } elseif($operator->getId()) {
                    if (array_key_exists('password', $param)) {
                        $operator->setData('password', $param['password']);
                    } else {
                        Mage::throwException(Mage::helper('udropship')->__("You don't send correct password"));
                    }
                } else {
                    Mage::throwException(Mage::helper('udropship')->__("You can't change the password"));
                }

                if (isset($param['onlyEditPassword'])) {
                    $session->addSuccess(Mage::helper('udropship')->__('Password has been saved'));
                } else {
                    $session->addSuccess(Mage::helper('udropship')->__('Settings has been saved'));
                }
            } catch (Exception $e) {
	            if(count($errorData)) {
		            $session->setData('vendorSettings', $errorData);
	            }
                $session->addError($e->getMessage());
            }
        }
        if (array_key_exists('redirectAfter', $param)) {
            $this->_redirect($param['redirectAfter']);
        } else {
            $this->_redirect('udropship/vendor_settings/info');
        }
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

                foreach (
                    array(
                        'company_name',
                        'dhl_rma', 'dhl_rma_account', 'dhl_rma_login', 'dhl_rma_password',
                        'orbaups_rma', 'orbaups_rma_account', 'orbaups_rma_login', 'orbaups_rma_password',
                        'vendor_attn',
                        'street', 'city', 'zip',
                        'rma_email','rma_telephone','rma_executive_telephone',
                        'rma_executive_telephone_mobile','rma_executive_email'
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


