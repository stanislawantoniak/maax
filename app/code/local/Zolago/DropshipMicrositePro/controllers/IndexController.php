<?php
require_once Mage::getModuleDir('controllers', "Unirgy_DropshipMicrositePro") . DS . "IndexController.php";

class Zolago_DropshipMicrositePro_IndexController 
	extends Unirgy_DropshipMicrositePro_IndexController
{
    public function indexAction()
    {
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();

        if ($vendor) {

            /* @var $campaignLandingPageHelper Zolago_Campaign_Helper_LandingPage */
            $campaignLandingPageHelper = Mage::helper("zolagocampaign/landingPage");
            $campaign = $campaignLandingPageHelper->getCampaign();

            if ($campaign->getIsLandingPage()
                && $campaign->getLandingPageContext() == Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_VENDOR
                && $campaign->getContextVendorId() == $vendor->getVendorId()
            ) {
                $landingPageCategory = $campaign->getLandingPageCategory();
                $this->_forward('view', "category", "catalog", array("id" => $landingPageCategory));
                return;
            }

            // Set root category
            $vendor->rootCategory();

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