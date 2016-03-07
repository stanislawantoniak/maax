<?php

/**
 * Class GH_GTM_Model_Observer
 */
class GH_GTM_Model_Observer {

	/**
	 * For easy use save info about last added product to cart
	 * used for GTM
	 * @see Orba_Common_Ajax_CartController::getDetailsForGTM
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function registerLastAddedProduct($observer) {
		$data = $observer->getData();
		unset($data['event']);
		Mage::register('gtm-last-added-product-info', $data);
	}
}
