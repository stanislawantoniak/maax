<?php

class Zolago_Dropship_Block_Vendor_Menu_Help extends Mage_Core_Block_Template
{

    const PREFIX_NAME = 'zolagoos';
    const PREFIX = self::PREFIX_NAME . '-help';

    public function getCmsBlockHelp() {
        $keys = $this->getKeys();
        $html = '';
        foreach ($keys as $key) {
            $_html = $this->getLayout()->createBlock('cms/block')->setBlockId($key)->toHtml();
            if (!empty($_html)) {
                $html = $_html;
            }
        }
        return $html;
    }

    private function localeCodeToShortString($code) {
        $_localeCodeToShortString = array(
            'pl_PL' => 'pl',
            'en_US' => 'en'
        );
        return isset($_localeCodeToShortString[$code]) ? $_localeCodeToShortString[$code] : null;
    }

    public function getKeys() {
        $lang       = $this->localeCodeToShortString(Mage::app()->getLocale()->getLocaleCode());
        $module     = Mage::app()->getFrontController()->getRequest()->getModuleName();
        if($module == "udropship")
            $module = self::PREFIX_NAME;

        $controller = Mage::app()->getFrontController()->getRequest()->getControllerName();
        $action     = Mage::app()->getFrontController()->getRequest()->getActionName();

        $arr        = array();
        $arr[]      = $this::PREFIX.'-'.$lang; // Default
        $arr[]      = $this::PREFIX.'-'.$lang.'-'.$module;
        $arr[]      = $this::PREFIX.'-'.$lang.'-'.$module.'-'.$controller;
        $arr[]      = $this::PREFIX.'-'.$lang.'-'.$module.'-'.$controller.'-'.$action;

        return array_map(array($this, 'clearKeys'), $arr);
    }

    public function getKeysToString() {
        $keys = $this->getKeys();
        return implode(' ', $keys);
    }

    public function clearKeys($value) {
        return str_replace('_','-', $value);
    }

}