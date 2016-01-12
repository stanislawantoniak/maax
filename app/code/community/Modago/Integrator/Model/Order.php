<?php

class Modago_Integrator_Model_Order
{
	protected $_shippingMethod = 'freeshipping_freeshipping';
	protected $_paymentMethod = 'cashondelivery';

	protected $_orderData = array();
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
		$this->initOrderData();
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


		//todo: fix shippings start
		//shipping cost
		$shippingCost = 0;
		$shippingCostBeforeDiscount = 0;
		$shippingCostDiscount = 0;
		foreach($apiOrder->order_items->item as $key=>$item) {
			if(!$item->is_delivery_item) {
				continue;
			} else {
				$shippingCost = round(floatval($item->item_value_after_discount),2);
				$shippingCostBeforeDiscount = round(floatval($item->item_value_before_discount),2);
				$shippingCostDiscount = round(floatval($item->item_discount),2);
				unset($apiOrder->order_items->item[$key]);
				break;
			}
		}

		$this->_order
			->setShippingAmount($shippingCost)
			->setBaseShippingAmount($shippingCost);
		//todo: fix shippings end

		//todo check: products
		$this->_addProducts($this->parseApiOrderProducts($apiOrder->order_items->item));

		/*if(round(floatval($apiOrder->order_total),2) != round(floatval($this->_orderData['subtotal']),2) + $shippingCost) {
			throw Mage::exception('Modago_Integrator',
				$apiHelper->__(
					'Calculated order total is not equal to the one sent by API. API: %s; Calculated: %s',
					$apiOrder->order_total,
					$this->_orderData['subtotal'] + $shippingCost
				));
		}*/
		$this->_order->setSubtotal($this->_orderData['subtotal'])
			->setBaseSubtotal($this->_orderData['subtotal'])
			->setGrandTotal($this->_orderData['subtotal'])
			->setBaseGrandTotal($this->_orderData['subtotal']);

		$this->_order->setData('noautopo_flag',1); //todo: remove it's for testing on local!

		$transaction->addObject($this->_order);
		$transaction->addCommitCallback(array($this->_order, 'place'));
		$transaction->addCommitCallback(array($this->_order, 'save'));
		$transaction->save();

		$this->_modagoOrderId = false;
		return $this->_order->getId();
	}

	protected function _addProducts($products)
	{
		foreach ($products as $productRequest) {
			$this->_addProduct($productRequest);
		}
	}

	protected function _addProduct($requestData)
	{
		$request = new Varien_Object();
		$request->setData($requestData);

		/** @var Mage_Catalog_Model_Product $product */
		$product = Mage::getModel('catalog/product');

		if($request['parent']) {
			$productId = $request['parent'];
			$product->load($productId);
			$superAttributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
			if(count($superAttributes)) {
				$superAttributeArray = array();
				foreach($superAttributes as $superAttribute) {
					$superAttributeId = $superAttribute->getAttributeId();
					$currentOptionId = Mage::getResourceModel('catalog/product')->getAttributeRawValue($request['product'],$superAttributeId,$this->_storeId);
					$superAttributeArray[$superAttributeId] = $currentOptionId;
				}
				$request->setData('super_attribute',$superAttributeArray);
			}
		} else {
			$productId = $request['product'];
			$product->load($productId);
		}

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

		foreach ($items as $item) {
			$this->_order->addItem($item);
		}

		$this->_order
			->setData('is_virtual',0);

		return $items;
	}

	function _productToOrderItem(Mage_Catalog_Model_Product $product, $apiData)
	{
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

		$orderItem = Mage::getModel('sales/order_item');

		$orderItem->setData($apiData['apiData']);

		$orderItem
			->setStoreId($this->_storeId)
			->setQuoteItemId(0)
			->setProductId($product->getId())
			->setProductType($product->getTypeId())
			->setProductOptions($options);

		if(isset($options['product_calculations'])) {
			$this->_orderData['subtotal'] += $apiData['apiData']['row_total_incl_tax'];
		}

		return $orderItem;
	}

	protected function initOrderData() {
		$this->_orderData = array(
			'subtotal' => 0.0
		);
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

				$taxPercent = $this->getProductTaxRate($product);
				$priceIncl = $item->item_value_after_discount;
				$originalPriceIncl = $item->item_value_before_discount;

				$price = round((($priceIncl / (100 + $taxPercent)) * 100),2); //price without tax
				$originalPrice = round((($originalPriceIncl / (100 + $taxPercent)) * 100),2);

				//parentSKU start
				$parent = false;
				$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
				if(is_array($parentIds) && count($parentIds)) {
					$parent = current($parentIds);
				}

				$rowTotal = $item->item_qty * $price;
				$taxAmountSingle = $priceIncl - $price;
				$rowTotalIncl = $item->item_qty * ($price + $taxAmountSingle);


				$parsed[] = array (
					'product'               => $product->getId(),
					'parent'                => $parent,

					'apiData' => array(
						'sku'                   => $item->item_sku,
						'name'                  => $item->item_name,

						'qty_ordered'           => $item->item_qty,
						'price'                 => $price,
						'base_price'            => $price,
						'original_price'        => $price,
						'base_original_price'   => $price,

						'tax_percent'           => $taxPercent,
						'tax_amount'            => $taxAmountSingle,
						'base_tax_amount'       => $taxAmountSingle,

						'discount_amount'       => $item->item_discount,
						'base_discount_amount'  => $item->item_discount,

						'row_total'             => $rowTotal,
						'base_row_total'        => $rowTotal,

						'price_incl_tax'        => $rowTotalIncl,
						'base_price_incl_tax'   => $rowTotalIncl,
						'row_total_incl_tax'    => $rowTotalIncl,
						'base_row_total_incl_tax'=> $rowTotalIncl
					)
				);
			} else {
				throw Mage::exception('Modago_Integrator',
					Mage::helper('modagointegrator/api')->__('Cannot find product with SKU: %s (Modago order id: %s)',$item->item_sku,$this->_modagoOrderId)
				);
			}
		}
		return $parsed;
	}

	/**
	 * gets tax for specified product in percent
	 * @param Mage_Catalog_Model_Product $product
	 * @return float
	 */
	protected function getProductTaxRate(Mage_Catalog_Model_Product $product) {
		//tax
		/** @var Mage_Tax_Model_Calculation $taxCalculation */
		$taxCalculation = Mage::getModel('tax/calculation');
		$request = $taxCalculation->getRateRequest(null, null, null, Mage::app()->getStore($this->_storeId));
		$taxClassId = $product->getTaxClassId();
		$percent = $taxCalculation->getRate($request->setProductClassId($taxClassId));

		return $percent ? $percent : 0.00;
	}
}
