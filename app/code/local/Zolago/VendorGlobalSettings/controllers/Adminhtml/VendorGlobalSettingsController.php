<?php
class Zolago_VendorGlobalSettings_Adminhtml_VendorGlobalSettingsController extends Mage_Adminhtml_Controller_Action{
	
	public function indexAction(){
		
		$this->loadLayout();
		$this->renderLayout();
		return $this;
	}
}
