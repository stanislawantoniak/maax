<?php
require_once Mage::getModuleDir('controllers', "Unirgy_DropshipMicrositePro") . DS . "IndexController.php";

class Zolago_DropshipMicrositePro_IndexController 
	extends Unirgy_DropshipMicrositePro_IndexController
{
    public function indexAction()
    {
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();

        if ($vendor) {
            // Set root category
            $vendorRootCategory = $vendor->rootCategory();
            $campaign = $vendorRootCategory->getCurrentCampaign();

            $fq = $this->getRequest()->getParam('fq', '');

            if ($campaign || !empty($fq)) {
                $this->_forward('view', "category", "catalog", array("id" => $vendorRootCategory->getId()));
                return;
            }

            $this->_forward('landingPage');
            return;
        }
        $this->_forward('index', 'index', 'cms');
    }

    public function landingpageAction(){
        $this->loadLayout();
		// Load after layout render
        $this->_initLayoutMessages(array('catalog/session', 'udqa/session', 'core/session'));
        $this->renderLayout();
    }
}