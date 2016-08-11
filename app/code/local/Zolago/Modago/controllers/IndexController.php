<?php

require_once Mage::getConfig()->getModuleDir('controllers', "ZolagoOs_OmniChannelMicrosite") . DS . "IndexController.php";

class Zolago_Modago_IndexController extends ZolagoOs_OmniChannelMicrosite_IndexController
{
    const ROUTE_STORE_MAPS = "zolagomodago_storesmap";


    public function indexAction()
    {
        $route = (string)Mage::getConfig()->getNode('frontend/routers/zolagomodago/args/frontName');

        if (Mage::app()->getRequest()->getRouteName() == self::ROUTE_STORE_MAPS) {
            $website = Mage::app()->getWebsite();
            if ($website->getHaveSpecificDomain() && $website->getVendorId()) {
                $this->_forward('index', "map", $route);
                return;
            } else {
                $this->_forward('defaultNoRoute');
                return;
            }
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