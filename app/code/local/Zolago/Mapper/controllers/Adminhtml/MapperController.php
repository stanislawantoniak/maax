<?php
class Zolago_Mapper_Adminhtml_MapperController 
	extends Mage_Adminhtml_Controller_Action{
	
	public function indexAction() {
		$this->loadLayout();
		$this->renderLayout();
	}
	
}