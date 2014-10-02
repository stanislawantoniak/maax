<?php
class Zolago_Help_ContactController extends Mage_Core_Controller_Front_Action
{
	public $isGallery = false;
	/**
	 * Display the contact help page
	 */
	public function vendorAction() {
		$this->loadLayout()->renderLayout();
	}

	public function galleryAction() {
		$this->isGallery = true;
		$this->loadLayout()->renderLayout();
	}

	public function isGallery() {
		return $this->isGallery ? true : false;
	}
}
