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
			$vendor->rootCategory();
			
            $this->_forward('landingPage');
            return;
        }
        $this->_forward('index', 'index', 'cms');
    }

    public function landingpageAction(){
        $this->_initLayoutMessages(array('catalog/session', 'udqa/session', 'core/session'));


        $this->loadLayout();
        $this->renderLayout();
    }
}