<?php

require_once Mage::getConfig()->getModuleDir('controllers', "ZolagoOs_OmniChannelMicrosite") . DS . "IndexController.php";

class Zolago_Modago_BrandsController extends ZolagoOs_OmniChannelMicrosite_IndexController
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}