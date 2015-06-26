<?php
require_once Mage::getModuleDir('controllers', "Mage_Catalog") . DS . "CategoryController.php";
class Zolago_Catalog_CategoryController extends Mage_Catalog_CategoryController {

	public function viewAction() {
		$this->getRequest()->setParam('q','');
		parent::viewAction();
	}
}