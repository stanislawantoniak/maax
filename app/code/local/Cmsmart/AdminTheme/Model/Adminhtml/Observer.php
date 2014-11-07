<?php

class Cmsmart_AdminTheme_Model_Adminhtml_Observer
{
    public function setTheme()
    {
        $theme = Mage::getStoreConfig('cmsmart_admintheme/config/theme');
        Mage::getDesign()->setTheme($theme);
        foreach (array('layout', 'template', 'skin', 'locale') as $type) {
            Mage::getDesign()->setTheme($type, $theme);
        }
    }
}