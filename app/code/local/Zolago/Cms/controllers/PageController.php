<?php
require_once Mage::getModuleDir('controllers', "Mage_Cms") . DS . "PageController.php";
class Zolago_Cms_PageController extends Mage_Cms_PageController
{
    public function preDispatch() {
        parent::preDispatch();
        Mage::dispatchEvent('cms_controller_page');
        return $this;
    }
}
