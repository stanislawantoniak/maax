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
		$quote = $observer->getEvent()->getDataObject();
		$quoteId = false;
		if ($quote instanceof Mage_Sales_Model_Quote) {
			$quoteId = $quote->getId();
			$cacheHelper->removeCacheCart($quoteId);
		}
		/** @var Mage_Checkout_Model_Session $checkoutSession */
		$checkoutSession = Mage::getSingleton('checkout/session');
		if (($checkoutQuoteId = $checkoutSession->getQuoteId()) != $quoteId) {
			$checkoutQuoteId = $checkoutQuoteId ? $checkoutQuoteId : false;
			$cacheHelper->removeCacheCart($checkoutQuoteId);
		}
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

	public function invalidateRecentlyViewedCustomerAjaxCache(Varien_Event_Observer $observer) {
		/** @var Orba_Common_Helper_Ajax_Customer_Cache $cacheHelper */
		$cacheHelper = Mage::helper('orbacommon/ajax_customer_cache');
		$cacheHelper->removeCacheRecentlyViewed();
	}

	public function invalidateVisitorHasSubscribedAjaxCache(Varien_Event_Observer $observer) {
		/** @var Orba_Common_Helper_Ajax_Customer_Cache $cacheHelper */
		$cacheHelper = Mage::helper('orbacommon/ajax_customer_cache');
		$cacheHelper->removeCacheVisitorHasSubscribed();
	}

}