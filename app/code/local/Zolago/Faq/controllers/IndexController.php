<?php

require_once Mage::getModuleDir('controllers', "Inic_Faq") . DS . "IndexController.php";

class Zolago_Faq_IndexController extends Inic_Faq_IndexController
{
    public function preDispatch() {
        parent::preDispatch();
        Mage::dispatchEvent('faq_controller_index');
        return $this;
    }
}
