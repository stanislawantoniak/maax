<?php

class Zolago_Sizetable_Dropship_SizetableController extends Zolago_Dropship_Controller_Vendor_Abstract {

	/**
	 * Sizetables listing action
	 */
	public function indexAction() {
		$this->render();
	}

	public function editAction() {
		$this->render();
	}

	public function saveAction() {

		$this->_redirectReferer();
	}

	protected function render() {
		$this->_renderPage(null,'zolagosizetable');
	}
}