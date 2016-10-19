<?php

class Orba_Common_Helper_Ajax_Customer_Cache extends Mage_Core_Helper_Abstract {

	const MAX_CART_ITEMS_COUNT = 5;

	const CACHE_NAME				= 'AJAX_CUSTOMER_';
	const CACHE_TAG					= 'CUSTOMER_AJAX_INFO';
	const CACHE_TAG_CART			= 'CUSTOMER_AJAX_INFO_CART';
	const CACHE_TAG_SEARCH			= 'CUSTOMER_AJAX_INFO_SEARCH';
	const CACHE_TAG_CUSTOMER_INFO	= 'CUSTOMER_AJAX_INFO_CUSTOMER';
	const CACHE_LIFE_TIME			= 900; // 15 min

	// temporary for store data for later save by $this->saveCustomerInfoCache()
	protected $customerInfo = array();

	/**
	 * Key building depends on logic in
	 * @see Zolago_Solrsearch_Helper_Data::getContextSelectorArray()
	 *
	 * @param array $params
	 * @return string
	 */
	public function getCacheKeyForSearch($params = array()) {
		/** @var ZolagoOs_OmniChannelMicrosite_Helper_Data $micrositeHelper */
		$micrositeHelper = Mage::helper('zolagodropshipmicrosite');
		/** @var Zolago_Dropship_Model_Vendor|false $vendor */
		$vendor = $micrositeHelper->getCurrentVendor();
		$vId = (int)($vendor && $vendor->getId()) ? $vendor->getId() : 0;
		if ($vId) {
			$key = self::CACHE_NAME . 'search_vendor-' . $vId;
			return $key;
		}
		$currentCategory = Mage::registry('current_category');
		$cId = (int)($currentCategory && $currentCategory->getId()) ? $currentCategory->getId() : (isset($params['category_id']) ? $params['category_id'] : 0);
		$sId = (int)Mage::app()->getStore()->getId();
		$rId = (int)Mage::app()->getStore()->getRootCategoryId();
		$key = self::CACHE_NAME . 'search_category-' . $cId . '-' .$sId . '-' . $rId;
		return $key;
	}

	/**
	 * @param int|bool $quoteId
	 * @return string
	 */
	public function getCacheKeyForCart($quoteId = false) {
		/** @var Mage_Checkout_Model_Session $checkoutSession */
		$checkoutSession = Mage::getSingleton('checkout/session');
		$quoteId = $quoteId ? $quoteId : $checkoutSession->getQuoteId();
		$key = self::CACHE_NAME . 'cart-' . (int)$quoteId;
		return $key;
	}

	/**
	 * @return string
	 */
	public function getCacheKeyForCustomerInfo() {
		/** @var Mage_Persistent_Helper_Session $persistentHelper */
		$persistentHelper = Mage::helper('persistent/session');
		$key = $persistentHelper->getSession()->getKey();

		if (!empty($key)) {
			$key = self::CACHE_NAME . 'customer-key-' . $key;
			return $key;
		} else {
			/** @var Zolago_Customer_Model_Session $customerSession */
			$customerSession = Mage::getSingleton('customer/session');
			/** @var Mage_Core_Model_Cookie $mCookie */
			$mCookie = Mage::getModel('core/cookie');
			$fronted = $mCookie->get($customerSession->getSessionName());
			$key = self::CACHE_NAME . 'customer-cookie-' . $fronted;
			return $key;
		}
	}

	/**
	 * @return array|false|mixed
	 */
	public function getCart() {
		/** @var Mage_Checkout_Model_Session $checkoutSession */
		$checkoutSession = Mage::getSingleton('checkout/session');
		$quoteId = (int)$checkoutSession->getQuoteId();
		if (!$quoteId) {
			// no quote so always nothing in cart
			$cart = array(
				'all_products_count'	=> 0,
				'products'				=> 0,
				'total_amount'			=> 0,
				'shipping_cost'			=> Mage::helper('core')->currency(0, true, false),
				'currency_symbol'		=> Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol()
			);
			return $cart;
		}

		$key = $this->getCacheKeyForCart();
		if ($this->canUseCache()) {
			$cacheData = $this->loadFromCache($key);
			if (!empty($cacheData)) {
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
			'all_products_count'	=> (int)Mage::helper('checkout/cart')->getSummaryCount(),
			'products'				=> $this->_getShoppingCartProducts(),
			'total_amount'			=> round(isset($totals["subtotal"]) ? $totals["subtotal"]->getValue() : 0, 2),
			'shipping_cost'			=> $zmcHelper->getFormattedShippingCostSummary(),
			'currency_symbol'		=> Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol()
		);
		$this->saveInCache($key, $cart, array(self::CACHE_TAG_CART));
		return $cart;
	}

	/**
	 * @param int|false $quoteId
	 * @return $this
	 */
	public function removeCacheCart($quoteId) {
		$key = $this->getCacheKeyForCart($quoteId);
		$cache = Mage::app()->getCache();
		$cache->remove($key);
		return $this;
	}

	/**
	 * Get and store info about favorites count
	 * and favorites product ids
	 *
	 * @param bool $force
	 * @return $this
	 */
	public function getFavoritesDetails($force = false) {

		if ((!isset($this->customerInfo['favorites_count']) || !isset($this->customerInfo['favorites_products'])) || $force) {
			// Favorites Count
			/** @var Zolago_Wishlist_Helper_Data $wishlistHelper */
			$wishlistHelper = Mage::helper('zolagowishlist');
			$wishlist = $wishlistHelper->getWishlist();
			$coll = $wishlist->getItemCollection();
			$data = $coll->getData();

			$wishlistProdIds = array();
			foreach ($data as $item) {
				/** @var Mage_Wishlist_Model_Item $item */
				$wishlistProdIds[$item['product_id']] = true;
			}
			$this->customerInfo = array_merge($this->customerInfo, array('favorites_count' => count($wishlistProdIds)));

			// Favorites products ids
			$this->customerInfo = array_merge($this->customerInfo, array('favorites_products' => $wishlistProdIds));
		}
		return $this;
	}

	/**
	 * @return int
	 */
	public function getFavoritesCount() {
		$key = $this->getCacheKeyForCustomerInfo();
		if ($this->canUseCache()) {
			$cacheData = $this->loadFromCache($key);
			if (!empty($cacheData)) {
				$this->customerInfo = array_merge($this->customerInfo, $cacheData);
			}
			if (isset($cacheData['favorites_count'])) {
				return $cacheData['favorites_count'];
			}
		}
		if(empty($this->customerInfo)){
			$this->getFavoritesDetails(true);
		} else {
			$this->getFavoritesDetails();
		}

		return $this->customerInfo['favorites_count'];
	}

	/**
	 * @return $this
	 */
	public function removeCacheFavoritesCount() {
		return $this->removeCacheCustomer('favorites_count');
	}

	public function getCustomerUtmData() {
		$key = $this->getCacheKeyForCustomerInfo();
		if ($this->canUseCache()) {
			$cacheData = $this->loadFromCache($key);
			if (!empty($cacheData)) {
				$this->customerInfo = array_merge($this->customerInfo, $cacheData);
			}
			if (isset($cacheData['utm_data'])) {
				return $cacheData['utm_data'];
			}
		}
		/** @var Zolago_Customer_Model_Session $customerSession */
		$customerSession = Mage::getSingleton('customer/session');
		/** @var Zolago_Customer_Model_Customer $customer */
		$customer = $customerSession->getCustomer();
		$this->customerInfo['utm_data'] = $customer->getUtmData(); // raw json
		return $this->customerInfo['utm_data'];
	}

	public function removeCacheCustomerUtmData() {
		return $this->removeCacheCustomer('utm_data');
	}

	/**
	 * Array of favorites products ids
	 *
	 * @return array
	 */
	public function getFavoritesProductsIds() {
		$key = $this->getCacheKeyForCustomerInfo();
		if ($this->canUseCache()) {
			$cacheData = $this->loadFromCache($key);
			if (!empty($cacheData)) {
				$this->customerInfo = array_merge($this->customerInfo, $cacheData);
			}
			if (isset($cacheData['favorites_products'])) {
				return $cacheData['favorites_products'];
			}
		}
		if(empty($this->customerInfo)){
			$this->getFavoritesDetails(true);
		} else {
			$this->getFavoritesDetails();
		}
		return $this->customerInfo['favorites_products'];
	}

	/**
	 * @return $this
	 */
	public function removeCacheFavoritesProductsIds() {
		return $this->removeCacheCustomer('favorites_products');
	}

	/**
	 * @return string
	 */
	public function getCustomerName() {
		$key = $this->getCacheKeyForCustomerInfo();
		if ($this->canUseCache()) {
			$cacheData = $this->loadFromCache($key);
			if (!empty($cacheData)) {
				$this->customerInfo = array_merge($this->customerInfo, $cacheData);
			}
			if (isset($cacheData['customer_name'])) {
				return $cacheData['customer_name'];
			}
		}
		/* @var $coreHelper Mage_Core_Helper_Data */
		$coreHelper = Mage::helper('core');
		/** @var Mage_Customer_Model_Session $session */
		$session = Mage::getSingleton('customer/session');
		return $this->customerInfo['customer_name'] = $coreHelper->escapeHtml($session->getCustomer()->getName());
	}

	/**
	 * @return $this
	 */
	public function removeCacheCustomerName() {
		return $this->removeCacheCustomer('customer_name');
	}

	/**
	 * @return string
	 */
	public function getCustomerEmail() {
		$key = $this->getCacheKeyForCustomerInfo();
		if ($this->canUseCache()) {
			$cacheData = $this->loadFromCache($key);
			if (!empty($cacheData)) {
				$this->customerInfo = array_merge($this->customerInfo, $cacheData);
			}
			if (isset($cacheData['customer_email'])) {
				return $cacheData['customer_email'];
			}
		}
		/* @var $coreHelper Mage_Core_Helper_Data */
		$coreHelper = Mage::helper('core');
		/** @var Mage_Customer_Model_Session $session */
		$session = Mage::getSingleton('customer/session');
		return $this->customerInfo['customer_email'] = $coreHelper->escapeHtml($session->getCustomer()->getEmail());
	}

	/**
	 * @return $this
	 */
	public function removeCacheCustomerEmail() {
		return $this->removeCacheCustomer('customer_email');
	}

	/**
	 * @return bool|string
	 */
	public function getVisitorHasSubscribed() {
		$key = $this->getCacheKeyForCustomerInfo();
		if ($this->canUseCache()) {
			$cacheData = $this->loadFromCache($key);
			if (!empty($cacheData)) {
				$this->customerInfo = array_merge($this->customerInfo, $cacheData);
			}
			if (isset($cacheData['visitorHasSubscribed'])) {
				return $cacheData['visitorHasSubscribed'];
			}
		}

		/** @var Zolago_Customer_Model_Session $customerSession */
		$customerSession = Mage::getSingleton('customer/session');
		$customerId = $customerSession->getCustomerId();
		$visitorHasSubscribed = false;
		if($customerId) {
			//visitorHasSubscribed
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');

			$query =
				'SELECT `subscriber_status` FROM `' .
				$resource->getTableName('newsletter/subscriber') .
				'` WHERE `customer_id` = ' . $customerId;

			$result = $readConnection->fetchAll($query);
			if (count($result)) {
				$newsletterStatus = current(current($result));
				switch ($newsletterStatus) {
					case Zolago_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED:
						$visitorHasSubscribed = 'yes';
						break;
					case Zolago_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED:
						$visitorHasSubscribed = 'unsubscribed';
						break;
				}
			}
		}
		return $this->customerInfo['visitorHasSubscribed'] = $visitorHasSubscribed;
	}

	/**
	 * @return $this
	 */
	public function removeCacheVisitorHasSubscribed() {
		return $this->removeCacheCustomer('visitorHasSubscribed');
	}

	/**
	 * @param $id
	 * @return $this
	 */
	public function removeCacheCustomer($id) {
		$key = $this->getCacheKeyForCustomerInfo();
		if ($this->canUseCache()) {
			$cacheData = $this->loadFromCache($key);
			if (!empty($cacheData)) {
				$this->customerInfo = array_merge($this->customerInfo, $cacheData);
			}
			if (isset($cacheData[$id])) {
				unset($cacheData[$id]);
				$cache = Mage::app()->getCache();
				$cache->remove($key);
				$this->saveCustomerInfoCache($cacheData);
			}
		}
		return $this;
	}

	/**
	 * Manually save part of cache connected only to customer
	 *
	 * @param array $customerInfo
	 * @return $this
	 */
	public function saveCustomerInfoCache($customerInfo = array()) {
		$key = $this->getCacheKeyForCustomerInfo();
		$this->customerInfo = $customerInfo;
		$this->saveInCache($key, $this->customerInfo, array(self::CACHE_TAG_CUSTOMER_INFO));
		return $this;
	}

	/**
	 * Retrieve recently viewed products info
	 *
	 * @param $skipProductId
	 * @return array
	 */
	public function getRecentlyViewed($skipProductId) {
		$key = $this->getCacheKeyForCustomerInfo();
		if ($this->canUseCache()) {
			$cacheData = $this->loadFromCache($key);
			if (!empty($cacheData)) {
				$this->customerInfo = array_merge($this->customerInfo, $cacheData);
			}
			if (isset($cacheData['recently_viewed'])) {
				// Don't show in last viewed box current product
				unset($cacheData['recently_viewed'][(int)$skipProductId]);
				return array_values($cacheData['recently_viewed']);
			}
		}

		/** @var Mage_Persistent_Helper_Session $persistentHelper */
		$persistentHelper = Mage::helper('persistent/session');

		/** @var Mage_Reports_Block_Product_Viewed $singleton */
		$singleton = Mage::getSingleton('Mage_Reports_Block_Product_Viewed');

		// By persistent
		if($persistentHelper->isPersistent() && $persistentHelper->getSession()->getCustomerId()){
			$customerId = $persistentHelper->getSession()->getCustomerId();
			$singleton->setCustomerId($customerId);
		}
		$recentlyViewedProducts = $singleton->getItemsCollection();

		/** @var Mage_Core_Helper_Data $coreHelper */
		$coreHelper = Mage::helper('core');

		$recentlyViewedContent = array();
		if ($recentlyViewedProducts->count() > 0) {
			foreach ($recentlyViewedProducts as $product) {
				/* @var $product Zolago_Catalog_Model_Product */
				$image = Mage::helper("zolago_image")
					->init($product, 'small_image')
					->setCropPosition(Zolago_Image_Model_Catalog_Product_Image::POSITION_CENTER)
					->adaptiveResize(200, 312);
				$recentlyViewedContent[(int)$product->getId()] = array(
					'title' => Mage::helper('catalog/output')->productAttribute($product, $product->getName(), 'name'),
					'image_url' => (string)$image,
					'redirect_url' => $product->getNoVendorContextUrl(),
					'price' => $coreHelper->currency($product->getFinalPrice(), true, false),
				);
				// add old price only if should be visible
				if ($product->getStrikeoutPrice() > $product->getPrice()) {
					$recentlyViewedContent[(int)$product->getId()]['old_price'] =
						$coreHelper->currency($product->getStrikeoutPrice(), true, false);
				}
			}
		}
		// Cache all recently viewed
		$this->customerInfo = $copy = array_merge($this->customerInfo, array('recently_viewed' => $recentlyViewedContent));

		// Don't show in last viewed box current product
		unset($copy['recently_viewed'][(int)$skipProductId]);
		return array_values($copy['recently_viewed']);
	}

	/**
	 * @return $this
	 */
	public function removeCacheRecentlyViewed() {
		return $this->removeCacheCustomer('recently_viewed');
	}

	/**
	 * @param $params
	 * @return array
	 */
	public function getSearch($params) {
		$key = $this->getCacheKeyForSearch($params);
		if ($this->canUseCache()) {
			$cacheData = $this->loadFromCache($key);
			if (!empty($cacheData)) {
				return $cacheData;
			}
		}
		/** @var Zolago_Solrsearch_Helper_Data $searchHelper */
		$searchHelper = Mage::helper('zolagosolrsearch');
		$searchContext = $searchHelper->getContextSelectorArray($params);
		$this->saveInCache($key, $searchContext, array(self::CACHE_TAG_SEARCH));
		return $searchContext;
	}

	/**
	 * @return $this
	 */
	public function removeCacheSearch() {
		$cache = Mage::app()->getCache();
		$ids = $cache->getIdsMatchingTags(array(self::CACHE_TAG_SEARCH));
		foreach ($ids as $id) {
			$cache->remove($id);
		}
		return $this;
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
	protected function _getProductOptions($item) {
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
	 * @param array $tags
	 * @return $this
	 */
	public function saveInCache($key, $data, $tags = array()) {
		if ($this->canUseCache()) {
			$cache = Mage::app()->getCache();
			$oldSerialization = $cache->getOption("automatic_serialization");
			$cache->setOption("automatic_serialization", true);
			$cache->save($data, $key, array_merge(array(self::CACHE_TAG), $tags), $this->getCacheLifeTime());
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

	/**
	 * Need to store in registry info about that is ajax context
	 * Purpose: for correct receiving data like current vendor
	 * @see Zolago_DropshipMicrosite_Helper_Protected::_getFrontendVendor()
	 *
	 * @return $this
	 */
	public function saveIsAjaxContext() {
		//set registry to correctly identify current context
		$ajaxReferrerUrlKey = 'ajax_referer_url';
		if (Mage::registry($ajaxReferrerUrlKey)) {
			Mage::unregister($ajaxReferrerUrlKey);
		}
		Mage::register($ajaxReferrerUrlKey, $this->_getRefererUrl());
		return $this;
	}

	/**
	 * @return $this
	 */
	public function removeAllCache() {
		$cache = Mage::app()->getCache();
		$ids = $cache->getIdsMatchingTags(array(self::CACHE_TAG));
		foreach ($ids as $id) {
			$cache->remove($id);
		}
		return $this;
	}
}