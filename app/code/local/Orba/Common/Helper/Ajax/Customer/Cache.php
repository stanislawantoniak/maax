<?php

class Orba_Common_Helper_Ajax_Customer_Cache extends Mage_Core_Helper_Abstract {

	const MAX_CART_ITEMS_COUNT = 5;

	const CACHE_NAME = 'AJAX_CUSTOMER_';
	const CACHE_TAG = 'CUSTOMER_AJAX_INFO';
	const CACHE_TAG_CART = 'CUSTOMER_AJAX_INFO_CART';
	const CACHE_LIFE_TIME = 900; // 15 min

	/**
	 * $what can be:
	 * 'CART'
	 * 'SEARCH'
	 *
	 * @param string $what
	 * @return string
	 * @throws Mage_Core_Exception
	 */
	public function getCacheKeyFor($what = '') {

		switch ($what) {
			case 'CART':
				/** @var Mage_Checkout_Model_Session $checkoutSession */
				$checkoutSession = Mage::getSingleton('checkout/session');
				$quoteId = $checkoutSession->getQuoteId();
				return self::CACHE_NAME . $what . (int)$quoteId;
			case 'SEARCH':
				/** @var Unirgy_DropshipMicrosite_Helper_Data $micrositeHelper */
				$micrositeHelper = Mage::helper('zolagodropshipmicrosite');
				/** @var Zolago_Dropship_Model_Vendor|false $vendor */
				$vendor = $micrositeHelper->getCurrentVendor();
				$vId = (int)($vendor && $vendor->getId()) ? $vendor->getId() : 0;
				$currentCategory = Mage::registry('current_category');
				$cId = (int)($currentCategory && $currentCategory->getId()) ? $currentCategory->getId() : (isset($contextData['category_id']) ? $contextData['category_id'] : 0);
				$rId = (int)Mage::app()->getStore()->getRootCategoryId();
				return self::CACHE_NAME . $what . $vId . '-' . $cId . '-' . $rId;
		}
		return '';
	}

	/**
	 * @return array|false|mixed
	 */
	public function getCart() {
		$key = $this->getCacheKeyFor('CART');
		if ($this->canUseCache()) {
			$cacheData = $this->loadFromCache($key);
			if ($cacheData) {
				return $cacheData;
			}
		}
		/** @var Mage_Checkout_Helper_Cart $ccHelper */
		$ccHelper = Mage::helper('checkout/cart');
		/** @var Zolago_Modago_Helper_Checkout $zmcHelper */
		$zmcHelper = Mage::helper('zolagomodago/checkout');
		/* @var $quote Zolago_Sales_Model_Quote */
		$quote = $ccHelper->getQuote();
		$totals = $quote->getTotals();
		$cart = array(
			'all_products_count'	=> Mage::helper('checkout/cart')->getSummaryCount(),
			'products'				=> $this->_getShoppingCartProducts(),
			'total_amount'			=> round(isset($totals["subtotal"]) ? $totals["subtotal"]->getValue() : 0, 2),
			'shipping_cost'			=> $zmcHelper->getFormattedShippingCostSummary(),
			'currency_symbol'		=> Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol()
		);
		$this->saveInCache($key, $cart);
		return $cart;
	}

	public function removeCacheCart() {
		$key = $this->getCacheKeyFor('CART');
		$cache = Mage::app()->getCache();
		$cache->remove($key);
	}

	public function getSearch($params) {
		$key = $this->getCacheKeyFor('SEARCH');
		if ($this->canUseCache()) {
			$cacheData = $this->loadFromCache($key);
			if ($cacheData) {
				return $cacheData;
			}
		}
		/** @var Zolago_Solrsearch_Helper_Data $searchHelper */
		$searchHelper = Mage::helper('zolagosolrsearch');
		$searchContext = $searchHelper->getContextSelectorArray($params);
		$this->saveInCache($key, $searchContext);
		return $searchContext;
	}

	public function removeCacheSearch() {
		//todo
	}

	/**
	 * @return bool
	 */
	public function canUseCache() {
		//return Mage::app()->useCache('???');
		return true;
	}

	/**
	 * @return array|int
	 */
	private function _getShoppingCartProducts() {

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
				$image = Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(40, 50);

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

	/**
	 * @param $item
	 * @return array
	 */
	private function _getProductOptions($item) {
		/* @var $product Mage_Catalog_Model_Product */
		$product = $item->getProduct();
		$options = $product->getTypeInstance(true)->getOrderOptions($product);

		$array = array();
		if ($options) {
			if (isset($options['attributes_info'])) {
				foreach ($options['attributes_info'] as $attrib) {
					$array[] = array(
						'label' => $attrib['label'],
						'value' => $attrib['value']
					);
				}
			}
		}
		return $array;
	}

	/**
	 * @param $key
	 * @param bool $unserialize
	 * @return false|mixed
	 */
	public function loadFromCache($key, $unserialize = true) {
		$cacheData = Mage::app()->getCache()->load($key);
		if ($unserialize) {
			return unserialize($cacheData);
		}
		return $cacheData;
	}

	/**
	 * @param $key
	 * @param $data
	 * @return $this
	 */
	public function saveInCache($key, $data) {
		if ($this->canUseCache()) {
			$cache = Mage::app()->getCache();
			$oldSerialization = $cache->getOption("automatic_serialization");
			$cache->setOption("automatic_serialization", true);
			$cache->save($data, $key, array(self::CACHE_TAG), $this->getCacheLifeTime());
			$cache->setOption("automatic_serialization", $oldSerialization);
		}
		return $this;
	}

	/**
	 * Return time in seconds
	 *
	 * @return int
	 */
	public function getCacheLifeTime() {
		return self::CACHE_LIFE_TIME;
	}
}