<?php

require_once Mage::getConfig()->getModuleDir('controllers', "Unirgy_DropshipMicrosite") . DS . "IndexController.php";

class Zolago_Modago_IndexController extends Unirgy_DropshipMicrosite_IndexController{
	public function indexAction() {

		$rootId = Mage::app()->getStore()->getRootCategoryId();
		$rootCategory = Mage::getModel("catalog/category")->load($rootId);
		$campaign = $rootCategory->getCurrentCampaign();

		$fq = $this->getRequest()->getParam('fq', '');

		if ($campaign || !empty($fq)) {
			$this->_forward('view', "category", "catalog", array("id" => $rootCategory->getId()));
			return;
		}

		if(Mage::helper('umicrosite')->getCurrentVendor()){
			return parent::indexAction();
		}
		
		$this->loadLayout();
		$this->renderLayout();
	}
}