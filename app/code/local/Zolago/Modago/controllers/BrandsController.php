<?php

require_once Mage::getConfig()->getModuleDir('controllers', "Unirgy_DropshipMicrosite") . DS . "IndexController.php";

class Zolago_Modago_BrandsController extends Unirgy_DropshipMicrosite_IndexController
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}