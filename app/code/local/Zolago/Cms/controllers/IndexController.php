<?php
require_once Mage::getModuleDir('controllers', "Mage_Cms") . DS . "IndexController.php";
class Zolago_Cms_IndexController extends Mage_Cms_IndexController
{
    public function preDispatch() {
        $out =  parent::preDispatch();
        Mage::dispatchEvent('cms_controller_page');
        return $out;
    }
}
