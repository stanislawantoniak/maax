<?php

class Orba_Common_Model_Observer {

	/**
	 * Remove cache for customer ajax
	 * on part with cart and products in it
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function invalidateCartCustomerAjaxCache(Varien_Event_Observer $observer) {
		/** @var Orba_Common_Helper_Ajax_Customer_Cache $cacheHelper */
		$cacheHelper = Mage::helper('orbacommon/ajax_customer_cache');
		$cacheHelper->removeCacheCart();
	}

	public function invalidateSearchCustomerAjaxCache(Varien_Event_Observer $observer) {
		/** @var Orba_Common_Helper_Ajax_Customer_Cache $cacheHelper */
		$cacheHelper = Mage::helper('orbacommon/ajax_customer_cache');
		$cacheHelper->removeCacheSearch();
	}

	public function invalidateWishlistCustomerAjaxCache(Varien_Event_Observer $observer) {
		/** @var Orba_Common_Helper_Ajax_Customer_Cache $cacheHelper */
		$cacheHelper = Mage::helper('orbacommon/ajax_customer_cache');
		$cacheHelper->removeCacheFavoritesCount();
		$cacheHelper->removeCacheFavoritesProductsIds();
	}
}