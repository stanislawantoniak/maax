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


	protected function _getCurrentProducts() {
		return $this->_getProducts(Mage::getSingleton("zolagosolrsearch/catalog_product_list"));
	}


	public function getProductsCache() {
		$currentProducts = $this->_getCurrentProducts();
		return $currentProducts;
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

	/**
	 * Set customer object and setting customer id in session
	 *
	 * @param   Mage_Customer_Model_Customer $customer
	 * @return  Mage_Customer_Model_Session
	 */
	public function setCustomer(Mage_Customer_Model_Customer $customer)
	{
		// check if customer is not confirmed
		if ($customer->isConfirmationRequired()) {
			if ($customer->getConfirmation()) {
				return $this->_logout();
			}
		}
		$this->_customer = $customer;
		$this->setId($customer->getId());
		$this->setCustomerId($customer->getId());
		// save customer as confirmed, if it is not
		if ((!$customer->isConfirmationRequired()) && $customer->getConfirmation()) {
			$customer->setConfirmation(null)->save();
			$customer->setIsJustConfirmed(true);
		}
		return $this;
	}
}
