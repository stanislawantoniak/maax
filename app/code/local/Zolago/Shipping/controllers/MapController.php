<?php

require_once Mage::getConfig()->getModuleDir('controllers', "ZolagoOs_OmniChannelMicrosite") . DS . "IndexController.php";

class Zolago_Shipping_MapController extends ZolagoOs_OmniChannelMicrosite_IndexController
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function getMapDataAction()
    {
        $filter = $this->getRequest()->getParam("filter", "");

        $block = new Zolago_Modago_Block_Map();
        echo $block->getMapData($filter);
        exit;
    }
}