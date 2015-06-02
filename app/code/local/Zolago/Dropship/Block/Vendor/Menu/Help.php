<?php

class Zolago_Dropship_Block_Vendor_Menu_Help extends Mage_Core_Block_Template
{

    public function getCmsBlockHelp() {
        $lang       = $this->localeCodeToShortString(Mage::app()->getLocale()->getLocaleCode());
        $module     = Mage::app()->getFrontController()->getRequest()->getModuleName();
        $controller = Mage::app()->getFrontController()->getRequest()->getControllerName();
        $action     = Mage::app()->getFrontController()->getRequest()->getActionName();

        return '[dev]' . $module . '-' . $controller . '-' . $action . '-' . $lang . '-' . 'key'; //TODO load cms block + installers
    }

    private function localeCodeToShortString($code) {
        $_localeCodeToShortString = array(
            'pl_PL' => 'pl',
            'en_US' => 'en'
        );
        return isset($_localeCodeToShortString[$code]) ? $_localeCodeToShortString[$code] : null;
    }
}