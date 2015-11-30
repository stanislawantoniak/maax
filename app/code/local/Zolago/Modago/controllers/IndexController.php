<?php

require_once Mage::getConfig()->getModuleDir('controllers', "Unirgy_DropshipMicrosite") . DS . "IndexController.php";

class Zolago_Modago_IndexController extends Unirgy_DropshipMicrosite_IndexController
{
    const ROUTE_STORE_MAPS = "zolagomodago_storesmap";


    public function indexAction()
    {

        if (Mage::app()->getRequest()->getRouteName() == self::ROUTE_STORE_MAPS) {
            $this->_forward('index', "map", "modago");
            return;
        }

        $rootId = Mage::app()->getStore()->getRootCategoryId();
        $rootCategory = Mage::getModel("catalog/category")->load($rootId);
        $campaign = $rootCategory->getCurrentCampaign();

        $fq = $this->getRequest()->getParam('fq', '');

        if ($campaign || !empty($fq)) {
            $this->_forward('view', "category", "catalog", array("id" => $rootCategory->getId()));
            return;
        }

        if (Mage::helper('umicrosite')->getCurrentVendor()) {
            return parent::indexAction();
        }

        $this->loadLayout();
        $this->renderLayout();
    }
}