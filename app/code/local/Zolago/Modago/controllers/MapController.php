<?php

require_once Mage::getConfig()->getModuleDir('controllers', "Unirgy_DropshipMicrosite") . DS . "IndexController.php";

class Zolago_Modago_MapController extends Unirgy_DropshipMicrosite_IndexController
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function getMapDataAction()
    {
        $filter = $this->getRequest()->getParam("filter", "");
        Mage::log($filter, null, "map.log");

        $block = new Zolago_Modago_Block_Map();
        echo $block->getMapData($filter);
        exit;
    }
}