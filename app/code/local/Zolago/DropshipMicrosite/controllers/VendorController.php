<?php

require_once Mage::getModuleDir('controllers', "Unirgy_DropshipMicrosite") . DS . "VendorController.php";

class Zolago_DropshipMicrosite_VendorController extends Unirgy_DropshipMicrosite_VendorController
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
        if (Mage::helper('udropship')->isModuleActive('Unirgy_DropshipVendorPortalUrl')) {
            Mage::getConfig()->setNode('global/models/core/rewrite/url', 'Unirgy_DropshipVendorPortalUrl_Model_Url');
        } else {
            Mage::getConfig()->setNode('global/models/core/rewrite/url', 'Unirgy_Dropship_Model_Url');
        }

    }


}