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
			$websiteId		= Mage::app()->getWebsite()->getId();
			$rootCategoryId = Mage::helper('zolagodropshipmicrosite')
					->getVendorRootCategory($vendor, $websiteId);
		
			$category = Mage::getModel("catalog/category")->load($rootCategoryId);
			
			if(!$category->getId()){
				$category->load(Mage::app()->getStore()->getRootCategoryId());
			}
			// Set vendor-context current category
			Mage::register('vendor_current_category', $category);
            $this->_forward('landingPage');
            return;
        }
        $this->_forward('index', 'index', 'cms');
    }
}