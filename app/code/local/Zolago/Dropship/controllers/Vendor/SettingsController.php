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




    //save
    public function infoPostAction()
    {
        $defaultAllowedTags = Mage::getStoreConfig('udropship/vendor/preferences_allowed_tags');
        $session = Mage::getSingleton('udropship/session');

        $r = $this->getRequest();

        if ($r->isPost()) {
            $p = $r->getPost();
            Zend_debug::dump($p);

            try {
                $v = $session->getVendor();

                $vendorId = $v->getVendorId();

                $vendorPreferences = Mage::getModel('zolagodropship/preferences');
                $vendorPreferences->load($vendorId, 'vendor_id');

                $data = array('vendor_id' => $vendorId);
                $data = array_merge($data, $p);

                $vendorPreferences->addData($data);

                $vendorPreferences->save();

                foreach (array(
                             'vendor_name', 'vendor_attn', 'email', 'password', 'telephone',
                             'street', 'city', 'zip', 'country_id', 'region_id',
                             'billing_vendor_attn', 'billing_email', 'billing_telephone',
                             'billing_street', 'billing_city', 'billing_zip', 'billing_country_id', 'billing_region_id'
                         ) as $f) {
                    if (array_key_exists($f, $p)) $v->setData($f, $p[$f]);
                }
                foreach (Mage::getConfig()->getNode('global/udropship/vendor/fields')->children() as $code=>$node) {
                    if (!isset($p[$code])) {
                        continue;
                    }
                    $param = $p[$code];
                    if (is_array($param)) {
                        foreach ($param as $key=>$val) {
                            $param[$key] = strip_tags($val, $defaultAllowedTags);
                        }
                    }
                    else {
                        $allowedTags = $defaultAllowedTags;
                        if ($node->filter_input && ($stripTags = $node->filter_input->strip_tags) && isset($stripTags->allowed)) {
                            $allowedTags = (string)$node->strip_tags->allowed;
                        }
                        if ($allowedTags && $node->type != 'wysiwyg') {
                            $param = strip_tags($param, $allowedTags);
                        }

                        if ($node->filter_input && ($replace = $node->filter_input->preg_replace) && isset($replace->from) && isset($replace->to)) {
                            $param = preg_replace((string)$replace->from, (string)$replace->to, $param);
                        }
                    } // end code injection protection
                    $v->setData($code, $param);
                }
                Mage::dispatchEvent('udropship_vendor_preferences_save_before', array('vendor'=>$v, 'post_data'=>&$p));
                $v->save();


                $session->addSuccess($this->__('Settings has been saved'));
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        }
        $this->_redirect('udropship/vendor_settings/info');
    }

}


