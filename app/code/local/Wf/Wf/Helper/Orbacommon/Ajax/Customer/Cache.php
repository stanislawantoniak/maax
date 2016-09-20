<?php

class Wf_Wf_Helper_Orbacommon_Ajax_Customer_Cache extends Orba_Common_Helper_Ajax_Customer_Cache {

	/**
	 * @return array|int
	 */
	protected function _getShoppingCartProducts() {

		/** @var Mage_Checkout_Model_Session $checkoutSession */
		$checkoutSession = Mage::getSingleton('checkout/session');
		/** @var Zolago_Sales_Model_Quote $quote */
		$quote = $checkoutSession->getQuote();
		$cartItems = $quote->getAllVisibleItems();

		// Show only couple of first products
		if (sizeof($cartItems) > self::MAX_CART_ITEMS_COUNT) {
			$cartItems = array_slice($cartItems, 0, self::MAX_CART_ITEMS_COUNT);
		}

		$array = array();
		foreach ($cartItems as $item) {
			/* @var $product Mage_Catalog_Model_Product */
			$product = $item->getProduct();

			if ($product && $product->getId()) {
				$options = $this->_getProductOptions($item);
				$image = Mage::helper('catalog/image')
					->init($product, 'thumbnail')
					->resize(80, 102);

				$array[] = array(
					'name'			=> $product->getName(),
					'url'			=> $product->getNoVendorContextUrl(),
					'qty'			=> $item->getQty(),
					'unit_price'	=> round($item->getPriceInclTax(), 2),
					'image_url'		=> (string)$image,
					'options'		=> $options
				);
			}
		}
		return !empty($array) ? $array : 0;
	}
}