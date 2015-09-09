<?php

class Zolago_Customer_Model_Session extends Mage_Customer_Model_Session
{
	const CURRENT_PRODUCTS_CATEGORY = 'curentProductsCategory';
	const CURRENT_PRODUCTS = 'currentProducts';
	const CURRENT_PRODUCTS_EXPIRE = 'currentProductsExpire';
	const CURRENT_PRODUCTS_EXPIRE_PATH = 'customer/listing_products_cache/expiration_time';

    public function __construct()
    {
	    parent::__construct();
    }

//	/**
//	 *
//	 * @param array $products
//	 */
//	public function addProductsToCache($products) {
//
//		$this->_clearProductsCache($products);
//
//		$currentCategory = Mage::registry('current_category');
//
//		if($currentCategory) {
//			$newCategory = $currentCategory->getId();
//			$prevCategory = $this->getData(self::CURRENT_PRODUCTS_CATEGORY);
//
//			$prevProducts = $this->getData(self::CURRENT_PRODUCTS);
//
//			if (!$prevCategory || !$prevProducts) {
//				$this
//					->setData(self::CURRENT_PRODUCTS_CATEGORY, $newCategory)
//					->setData(self::CURRENT_PRODUCTS, $products);
//			} elseif ($prevProducts['start'] < $products['start']) {
//				$newProducts = $products;
//				$newProducts['products'] = array_merge($prevProducts['products'], $newProducts['products']);
//				$this->setData(self::CURRENT_PRODUCTS, $newProducts);
//			}
//
//			$this->_setCurrentProductsExpire();
//		}
//
//		return $this->getData(self::CURRENT_PRODUCTS);
//	}

	protected function _getCurrentProducts() {
		return $this->_getProducts(Mage::getSingleton("zolagosolrsearch/catalog_product_list"));
	}

//	protected function _clearProductsCache($products) {
//		$newCategory = Mage::registry('current_category');
//		if(is_object($newCategory)) {
//			$newCategory = $newCategory->getId();
//		} else {
//			return $this;
//		}
//		$prevCategory = $this->getData(self::CURRENT_PRODUCTS_CATEGORY);
//		$prevProducts = $this->getData(self::CURRENT_PRODUCTS);
//		$prevProductsExpire = $this->getData(self::CURRENT_PRODUCTS_EXPIRE);
//
//		if($newCategory != $prevCategory ||
//			is_null($prevProducts) ||
//			is_null($prevProductsExpire) ||
//			$prevProductsExpire - time() < 0 ||
//			!isset($prevProducts['dir']) || $prevProducts['dir'] != $products['dir'] ||
//			!isset($prevProducts['sort']) || $prevProducts['sort'] != $products['sort'] ||
//			!isset($prevProducts['query']) || $prevProducts['query'] != $products['query'] ||
//			!isset($prevProducts['total']) || $prevProducts['total'] != $products['total'])
//		{ //clear products if user is looking at another category, changed sorting or search query
//			$this->unsetData(self::CURRENT_PRODUCTS_CATEGORY)->unsetData(self::CURRENT_PRODUCTS)->unsetData(self::CURRENT_PRODUCTS_EXPIRE);
//		}
//		return $this;
//	}

//	protected function _setCurrentProductsExpire() {
//		$expirationMinutes = Mage::getStoreConfig(self::CURRENT_PRODUCTS_EXPIRE_PATH);
//		if(!$expirationMinutes || !is_int($expirationMinutes)) {
//			$expirationMinutes = 15;
//		}
//		$expirationTime = time()+($expirationMinutes*60);
//		return $this->setData(self::CURRENT_PRODUCTS_EXPIRE, $expirationTime);
//	}

	public function getProductsCache() {
		$currentProducts = $this->_getCurrentProducts();
		return $currentProducts;
		//$this->_clearProductsCache($currentProducts);
//		return $this->getData(self::CURRENT_PRODUCTS) ? $this->getData(self::CURRENT_PRODUCTS) : $this->addProductsToCache($currentProducts);
	}

	/**
	 *
	 * @param Zolago_Solrsearch_Model_Catalog_Product_List $listModel
	 * @param type $param
	 * @return type
	 */
	protected function _getSolrParam(Zolago_Solrsearch_Model_Catalog_Product_List $listModel, $param) {
		if (is_null($out = $listModel->getCollection()->getSolrData('request', 'responseHeader', 'params', $param))) {
			$out = $listModel->getCollection()->getSolrData('responseHeader', 'params', $param);
		}
		return $out;
	}

	/**
	 * @param Zolago_Solrsearch_Model_Catalog_Product_List $listModel
	 * @return array
	 */
	protected function _getProducts(Zolago_Solrsearch_Model_Catalog_Product_List $listModel) {

		//$profiler = Mage::helper("zolagocommon/profiler");
		/* @var $profiler Zolago_Common_Helper_Profiler */
		//$profiler->start();

		/** @var Zolago_Solrsearch_Helper_Data $_solrHelper */
		$_solrHelper = Mage::helper("zolagosolrsearch");

		$query = str_replace('*','',$this->_getSolrParam($listModel, 'q'));

		return array(
			"total"			=> (int)$listModel->getCollection()->getSize(),
			"start"			=> (int)$this->_getSolrParam($listModel, 'start'),
			"rows"			=> (int)$this->_getSolrParam($listModel, 'rows'),
			"query"			=> $query,
			"sort"			=> $listModel->getCurrentOrder(),
			"dir"			=> $listModel->getCurrentDir(),
			"products"		=> $_solrHelper->prepareAjaxProducts($listModel),
		);
	}
}
