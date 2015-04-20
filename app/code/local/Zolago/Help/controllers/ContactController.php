<?php
/**
 * Display the contact help page
 */
class Zolago_Help_ContactController extends Mage_Core_Controller_Front_Action
{
	public $isGallery = false;

    public function preDispatch() {
        parent::preDispatch();
        Mage::dispatchEvent('help_controller_contact');
        return $this;
    }

	public function vendorAction() {
		$this->loadLayout()->_initLayoutMessages('udqa/session')->renderLayout();
	}

	public function galleryAction() {
		$this->loadLayout()->_initLayoutMessages('udqa/session')->renderLayout();
	}
}
