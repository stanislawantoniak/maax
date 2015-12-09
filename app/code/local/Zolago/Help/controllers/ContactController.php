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
		$this->renderPage(true);
	}

	public function galleryAction() {
		$this->renderPage(true);
	}

	public function indexAction() {
		$this->renderPage(false);
	}

	protected function renderPage($shouldBeGallery) {
		if($this->isGallery() != $shouldBeGallery) {
			$this->loadLayout()->_initLayoutMessages('udqa/session')->renderLayout();
		} else {
			$this->norouteAction();
			return;
		}
	}

	protected function isGallery() {
		return Mage::app()->getWebsite()->getHaveSpecificDomain();
	}
}
