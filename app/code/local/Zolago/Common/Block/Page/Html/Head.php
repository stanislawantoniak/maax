<?php

class Zolago_Common_Block_Page_Html_Head extends Mage_Page_Block_Html_Head {

    /**
     * Add locale skin_js depend on current lang
     *
     * @param string $type
     * @param array $name
     */
    public function addLocaleJs($type = 'skin_js', $name = array()) {
        $selectedLang = Mage::app()->getLocale()->getLocaleCode();
        if (isset($name[$selectedLang]) && $name[$selectedLang]) {
            $this->addItem($type, $name[$selectedLang]);
        }
    }
}