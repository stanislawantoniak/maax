<?php
class Zolago_Help_ContactController extends Mage_Core_Controller_Front_Action
{
	public $isGallery = false;
	/**
	 * Display the contact help page
	 */

	public function vendorAction() {
		$this->loadLayout()->_initLayoutMessages('udqa/session')->renderLayout();
	}

	public function galleryAction() {
		$this->loadLayout()->_initLayoutMessages('udqa/session')->renderLayout();
	}
}
