<?php

require_once Mage::getConfig()->getModuleDir('controllers', "Unirgy_DropshipMicrosite") . DS . "IndexController.php";

class Zolago_Modago_IndexController extends Unirgy_DropshipMicrosite_IndexController{
	public function indexAction() {

		/* @var $campaignLandingPageHelper Zolago_Campaign_Helper_LandingPage */
		$campaignLandingPageHelper = Mage::helper("zolagocampaign/landingPage");
		$campaign = $campaignLandingPageHelper->getCampaign();


		$rootId = Mage::app()->getStore()->getRootCategoryId();
		if ($campaign->getIsLandingPage()
			&& $campaign->getLandingPageContext() == Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_GALLERY
			&& $campaign->getLandingPageCategory() == $rootId
		) {
			$landingPageCategory = $campaign->getLandingPageCategory();
			$this->_forward('view', "category", "catalog", array("id" => $landingPageCategory));
			return;
		}


		if(Mage::helper('umicrosite')->getCurrentVendor()){
			return parent::indexAction();
		}
		
		$this->loadLayout();
		$this->renderLayout();
	}
}