<?php
require_once Mage::getModuleDir('controllers','Mage_Newsletter').DS.'ManageController.php';
class Zolago_Newsletter_ManageController extends Mage_Newsletter_ManageController {
	public function saveAction() {
		parent::saveAction();
		$this->_redirectReferer();
	}
}