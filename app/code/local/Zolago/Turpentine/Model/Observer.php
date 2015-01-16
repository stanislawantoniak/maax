<?php
class Zolago_Turpentine_Model_Observer {
	/**
	 * Handle caching products
	 * @todo remove after full cache ready
	 * @area: frontend
	 * @event: catalog_controller_product_view
	 * @param Varien_Event_Observer $observer
	 */
	public function productView(Varien_Event_Observer $observer) {
		Mage::register('turpentine_nocache_flag', 0); // allow to cache
	}
}