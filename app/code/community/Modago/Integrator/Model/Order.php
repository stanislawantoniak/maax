<?php

class Modago_Integrator_Model_Order
{
	protected $_shippingMethod = 'freeshipping_freeshipping';
	protected $_paymentMethod = 'cashondelivery';

	protected $_subTotal = 0;
	protected $_order;
	protected $_storeId;
	protected $_modagoOrderId = false;
	protected $_modagoVendorId = false;

	public function setShippingMethod($methodName)
	{
		$this->_shippingMethod = $methodName;
	}

	public function setPaymentMethod($methodName)
	{
		$this->_paymentMethod = $methodName;
	}

	/**
	 * @param object $apiOrder
	 * @throws Exception
	 * @returns Boolean
	 */
	public function createOrderFromApi($apiOrder)
	{
		$this->_modagoOrderId = $apiOrder->order_id;
		$this->_modagoVendorId = $apiOrder->vendor_id;

		/** @var Modago_Integrator_Helper_Api $apiHelper */
		$apiHelper = Mage::helper("modagointegrator/api");

		$transaction = Mage::getModel('core/resource_transaction');
		$this->_storeId = $apiHelper->getStoreId();
		$reservedOrderId = Mage::getSingleton('eav/config')
			->getEntityType('order')
			->fetchNewIncrementId($this->_storeId);

		$currencyCode  = $apiOrder->order_currency;

		$this->_order = Mage::getModel('sales/order')
			->setIncrementId($reservedOrderId)
			->setStoreId($this->_storeId)
			->setQuoteId(0)
			->setGlobalCurrencyCode($currencyCode)
			->setBaseCurrencyCode($currencyCode)
			->setStoreCurrencyCode($currencyCode)
			->setOrderCurrencyCode($currencyCode)
			->setModagoOrderId($this->_modagoOrderId);


		$this->_order->setCustomerEmail($apiOrder->order_email)
			->setCustomerFirstname($apiOrder->delivery_data->delivery_address->delivery_first_name)
			->setCustomerLastname($apiOrder->delivery_data->delivery_address->delivery_last_name)
			->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
			->setCustomerIsGuest(1);

		if($apiOrder->invoice_data->invoice_address && count($apiOrder->invoice_data->invoice_address)) {
			$billingAddress = Mage::getModel('sales/order_address')
				->setStoreId($this->_storeId)
				->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
				->setFirstname($apiOrder->invoice_data->invoice_address->invoice_first_name)
				->setLastname($apiOrder->invoice_data->invoice_address->invoice_last_name)
				->setCompany($apiOrder->invoice_data->invoice_address->invoice_company_name)
				->setStreet($apiOrder->invoice_data->invoice_address->invoice_street)
				->setCity($apiOrder->invoice_data->invoice_address->invoice_city)
				->setCountry_id($apiOrder->invoice_data->invoice_address->invoice_country)
				->setPostcode($apiOrder->invoice_data->invoice_address->invoice_zip_code)
				->setTelephone($apiOrder->invoice_data->invoice_address->phone);
		} else {
			$billingAddress = Mage::getModel('sales/order_address')
				->setStoreId($this->_storeId)
				->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
				->setFirstname($apiOrder->delivery_data->delivery_address->delivery_first_name)
				->setLastname($apiOrder->delivery_data->delivery_address->delivery_last_name)
				->setCompany($apiOrder->delivery_data->delivery_address->delivery_company_name)
				->setStreet($apiOrder->delivery_data->delivery_address->delivery_street)
				->setCity($apiOrder->delivery_data->delivery_address->delivery_city)
				->setCountry_id($apiOrder->delivery_data->delivery_address->delivery_country)
				->setPostcode($apiOrder->delivery_data->delivery_address->delivery_zip_code)
				->setTelephone($apiOrder->delivery_data->delivery_address->phone);
		}
		$this->_order->setBillingAddress($billingAddress);

		$shippingAddress = Mage::getModel('sales/order_address')
			->setStoreId($this->_storeId)
			->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
			->setFirstname($apiOrder->delivery_data->delivery_address->delivery_first_name)
			->setLastname($apiOrder->delivery_data->delivery_address->delivery_last_name)
			->setCompany($apiOrder->delivery_data->delivery_address->delivery_company_name)
			->setStreet($apiOrder->delivery_data->delivery_address->delivery_street)
			->setCity($apiOrder->delivery_data->delivery_address->delivery_city)
			->setCountry_id($apiOrder->delivery_data->delivery_address->delivery_country)
			->setPostcode($apiOrder->delivery_data->delivery_address->delivery_zip_code)
			->setTelephone($apiOrder->delivery_data->delivery_address->phone);

		$this->_order->setShippingAddress($shippingAddress)
			->setShippingMethod($apiHelper->getShippingMethodByApi($apiOrder->delivery_method));

		$orderPayment = Mage::getModel('sales/order_payment')
			->setStoreId($this->_storeId)
			->setCustomerPaymentId(0)
			->setMethod($apiHelper->getPaymentMethodByApi($apiOrder->payment_method))
			->setPoNumber(' – ');

		$this->_order->setPayment($orderPayment);

		//todo check: products
		$this->_addProducts($this->parseApiOrderProducts($apiOrder->order_items->item));

		if(round(floatval($apiOrder->order_total),2) != round(floatval($this->_subTotal),2)) {
			throw Mage::exception('Modago_Integrator',
				$apiHelper->__(
					'Calculated order total is not equal to the one sent by API. API: %s; Calculated: %s',
					$apiOrder->order_total,
					$this->_subTotal
				));
		}
		$this->_order->setSubtotal($this->_subTotal)
			->setBaseSubtotal($this->_subTotal)
			->setGrandTotal($this->_subTotal)
			->setBaseGrandTotal($this->_subTotal);

		$this->_order->setUdpoNoSplitPoFlag(1); //todo: remove it's for testing on local!

		$transaction->addObject($this->_order);
		$transaction->addCommitCallback(array($this->_order, 'place'));
		$transaction->addCommitCallback(array($this->_order, 'save'));
		$transaction->save();

		$this->_modagoOrderId = false;
		return $this->_order->getId();
	}

	protected function _addProducts($products)
	{
		$this->_subTotal = 0;
		foreach ($products as $productRequest) {
			$this->_addProduct($productRequest);
		}
	}

	protected function _addProduct($requestData)
	{
		$request = new Varien_Object();
		$request->setData($requestData);

		$product = Mage::getModel('catalog/product')->load($request['product']);

		$cartCandidates = $product->getTypeInstance(true)
			->prepareForCartAdvanced($request, $product);

		if (is_string($cartCandidates)) {
			throw new Exception($cartCandidates);
		}

		if (!is_array($cartCandidates)) {
			$cartCandidates = array($cartCandidates);
		}

		$parentItem = null;
		$errors = array();
		$items = array();
		foreach ($cartCandidates as $candidate) {
			$item = $this->_productToOrderItem(
				$candidate,
				$candidate->getCartQty(),
				$request
			);

			$items[] = $item;

			/**
			 * As parent item we should always use the item of first added product
			 */
			if (!$parentItem) {
				$parentItem = $item;
			}
			if ($parentItem && $candidate->getParentProductId()) {
				$item->setParentItem($parentItem);
			}
			/**
			 * We specify qty after we know about parent (for stock)
			 */
			$item->setQty($item->getQty() + $candidate->getCartQty());

			// collect errors instead of throwing first one
			if ($item->getHasError()) {
				$message = $item->getMessage();
				if (!in_array($message, $errors)) { // filter duplicate messages
					$errors[] = $message;
				}
			}
		}
		if (!empty($errors)) {
			Mage::throwException(implode("\n", $errors));
		}

		foreach ($items as $item){
			$this->_order->addItem($item);
		}

		return $items;
	}

	function _productToOrderItem(Mage_Catalog_Model_Product $product, $qty = 1, $apiData)
	{
		$rowTotal = $apiData['value_after_discount'] * $qty;

		$options = $product->getCustomOptions();

		$optionsByCode = array();

		foreach ($options as $option)
		{
			$quoteOption = Mage::getModel('sales/quote_item_option')->setData($option->getData())
				->setProduct($option->getProduct());

			$optionsByCode[$quoteOption->getCode()] = $quoteOption;
		}

		$product->setCustomOptions($optionsByCode);

		$options = $product->getTypeInstance(true)->getOrderOptions($product);

		$orderItem = Mage::getModel('sales/order_item')
			->setStoreId($this->_storeId)
			->setQuoteItemId(0)
			->setQuoteParentItemId(NULL)
			->setProductId($product->getId())
			->setProductType($product->getTypeId())
			->setQtyBackordered(NULL)
			->setTotalQtyOrdered($product['rqty'])
			->setQtyOrdered($product['qty'])
			->setName($product->getName())
			->setSku($product->getSku())
			->setPrice($apiData['value_after_discount'])
			->setBasePrice($apiData['value_after_discount'])
			->setOriginalPrice($apiData['value_before_discount']) //todo: check prices
			//->setDiscount($apiData['discount']) //todo: save discount
			->setRowTotal($rowTotal)
			->setBaseRowTotal($rowTotal)

			->setWeeeTaxApplied(serialize(array()))
			->setBaseWeeeTaxDisposition(0)
			->setWeeeTaxDisposition(0)
			->setBaseWeeeTaxRowDisposition(0)
			->setWeeeTaxRowDisposition(0)
			->setBaseWeeeTaxAppliedAmount(0)
			->setBaseWeeeTaxAppliedRowAmount(0)
			->setWeeeTaxAppliedAmount(0)
			->setWeeeTaxAppliedRowAmount(0)

			->setProductOptions($options);

		$this->_subTotal += $rowTotal;

		return $orderItem;
	}

	protected function parseApiOrderProducts($apiOrderProducts) {
		$parsed = array();
		foreach($apiOrderProducts as $item) {
			if(!$item->item_sku) {
				continue; //todo: fix shipping costs
			}

			/** @var Mage_Catalog_Model_Product $productModel */
			$productModel = Mage::getModel('catalog/product');

			$productId = $productModel->getIdBySku($item->item_sku);

			if($productId) {
				/** @var Mage_Catalog_Model_Product $product */
				$product = $productModel->load($productId);
				$parsed[] = array (
					'product'               => $product->getId(),
					'sku'                   => $item->item_sku,
					'name'                  => $item->item_name,
					'qty'                   => $item->item_qty,
					'value_before_discount' => $item->item_value_before_discount,
					'discount'              => $item->item_discount,
					'value_after_discount'  => $item->item_value_after_discount
				);
			} else {
				throw Mage::exception('Modago_Integrator',
					Mage::helper('modagointegrator/api')->__('Cannot find product with SKU: %s (Modago order id: %s)',$item->item_sku,$this->_modagoOrderId)
				);
			}
		}
		return $parsed;
	}
}
