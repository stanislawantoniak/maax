<?php

class Modago_Integrator_Model_Order {
	protected $_storeId;
	protected $_helper;

	protected $_apiShippingCost = 0;
	protected $_modagoOrderId;
	protected $_productStocks = array();
	protected $_productProblems = array();

	public function __construct() {
		$this->_helper = Mage::helper("modagointegrator/api");
		$this->_storeId = $this->getHelper()->getStoreId();
	}

	/**
	 * @return Modago_Integrator_Helper_Api
	 */
	public function getHelper() {
		return $this->_helper;
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


	public function createOrder($apiOrder) {
		//set vars needed for proper logging:
		$this->_modagoOrderId = $apiOrder->order_id;
		$this->_order = new stdClass();

		$products = $this->getProducts($apiOrder);

		//return 0;

		// Start New Sales Order Quote
		/** @var Mage_Sales_Model_Quote $quote */
		$quote = Mage::getModel('sales/quote')
			->setStore($this->getStore())
			->setCurrency($apiOrder->order_currency)
			->setCustomerEmail($apiOrder->order_email);

		//guest order setup
		$quote
			->setCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST)
			->setCustomerId(null)
			->setCustomerEmail($apiOrder->order_email)
			->setCustomerIsGuest(true)
			->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);

		/*$customer = Mage::getModel('customer/customer')
			->setWebsiteId($websiteId)
			->loadByEmail($email);
		if($customer->getId()==""){
			$customer = Mage::getModel('customer/customer');
			$customer->setWebsiteId($websiteId)
				->setStore($store)
				->setFirstname('Jhon')
				->setLastname('Deo')
				->setEmail($email)
				->setPassword("password");
			$customer->save();
		}
		// Assign Customer To Sales Order Quote
		$quote->assignCustomer($customer);*/

		// Configure Notification
		/*$quote->setSendConfirmation(1);*/

		foreach($products as $productData){
			$requestArray = array(
				'qty'   => $productData['qty'],
			);

			if(isset($productData['super_attribute'])) {
				$requestArray['super_attribute'] = $productData['super_attribute'];
			}

			$quote->addProduct(
				$productData['model'],
				new Varien_Object($requestArray)
			);
		}

		// Set Sales Order Billing Address
		if($apiOrder->invoice_data->invoice_required) {
			$rawBillingData = $apiOrder->invoice_data->invoice_address;
			$strToReplaceInKey = 'invoice_';
		} else {
			$rawBillingData = $apiOrder->delivery_data->delivery_address;
			$strToReplaceInKey = 'delivery_';
		}
		$billingData = array();
		foreach($rawBillingData as $key => $value) {
			$billingData[str_replace($strToReplaceInKey,"",$key)] = $value;
		}

		// Set Sales Order Shipping Address
		$shippingData = array();
		foreach($apiOrder->delivery_data->delivery_address as $key => $value) {
			$shippingData[str_replace('delivery_',"",$key)] = $value;
		}

		$billingAddress = $quote->getBillingAddress()->addData(array(
			'prefix'                => '',
			'firstname'             => isset($billingData['first_name']) ? $billingData['first_name'] : '',
			'middlename'            => '',
			'lastname'              => isset($billingData['last_name']) ? $billingData['last_name'] : '',
			'suffix'                => '',
			'company'               => isset($billingData['company_name']) ? $billingData['company_name'] : '',
			'street'                => isset($billingData['street']) ? $billingData['street'] : '',
			'city'                  => isset($billingData['city']) ? $billingData['city'] : '',
			'country_id'            => isset($billingData['country']) ? $billingData['country'] : '',
			'region'                => '',
			'postcode'              => isset($billingData['zip_code']) ? $billingData['zip_code'] : '',
			'telephone'             => isset($shippingData['phone']) ? $shippingData['phone'] : '',
			'fax'                   => '',
			'vat_id'                => isset($billingData['tax_id']) ? $billingData['tax_id'] : '',
			'save_in_address_book'  => 1
		));

		$shippingAddress = $quote->getShippingAddress()->addData(array(
			'prefix'                => '',
			'firstname'             => isset($shippingData['first_name']) ? $shippingData['first_name'] : '',
			'middlename'            => '',
			'lastname'              => isset($shippingData['last_name']) ? $shippingData['last_name'] : '',
			'suffix'                => '',
			'company'               => isset($shippingData['company_name']) ? $shippingData['company_name'] : '',
			'street'                => isset($shippingData['street']) ? $shippingData['street'] : '',
			'city'                  => isset($shippingData['city']) ? $shippingData['city'] : '',
			'country_id'            => isset($shippingData['country']) ? $shippingData['country'] : '',
			'region'                => '',
			'postcode'              => isset($shippingData['zip_code']) ? $shippingData['zip_code'] : '',
			'telephone'             => isset($shippingData['phone']) ? $shippingData['phone'] : '',
			'fax'                   => '',
			'vat_id'                => '', //not provided by api in delivery address
			'save_in_address_book'  => 1
		));
		// Collect Rates and Set Shipping & Payment Method
		$shippingMethod = $this->getShippingMethod($apiOrder->delivery_method);
		$paymentMethod = $this->getPaymentMethod($apiOrder->payment_method);

		Mage::unregister(Modago_Integrator_Model_Payment_Zolagopayment::PAYMENT_METHOD_ACTIVE_REGISTRY_KEY);
		Mage::register(Modago_Integrator_Model_Payment_Zolagopayment::PAYMENT_METHOD_ACTIVE_REGISTRY_KEY,true);

		$shippingAddress
			->setCollectShippingRates(true)
			->collectShippingRates()
			->setShippingMethod($shippingMethod)
			->setPaymentMethod($paymentMethod);

		// Set Sales Order Payment
		$quote->getPayment()->importData(array('method' => $paymentMethod));

		// Collect Totals & Save Quote
		$quote->collectTotals()->save();

		// Create Order From Quote
		/** @var Mage_Sales_Model_Service_Quote $service */
		$service = Mage::getModel('sales/service_quote', $quote);
		$service->submitAll();

		/** @var Mage_Sales_Model_Order $order */
		$order = $service->getOrder();
		$increment_id = $order->getRealOrderId();

		//set paid amount
		if($apiOrder->payment_method != 'cash_on_delivery') {
			$total = floatval($apiOrder->order_total);
			$totalPaid = round(($total - floatval($apiOrder->order_due_amount)), 2);
			$order->setTotalPaid($totalPaid);
			if ($totalPaid >= $total) {
				$order->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
			} else {
				$order->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
			}
		}

		$order->setModagoOrderId($this->_modagoOrderId)->save();
		// check products stats
		$this->_checkProductStocks();

		// Resource Clean-Up
		$quote = $service = $order = null;

		// Finished
		return $increment_id;

	}

	
    /**
     * check reservations
     */

	protected function _checkProductStocks() {
        foreach ($this->_productStocks as $productId) {
	        $product = $this->getProduct($productId);
            $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
            if ($qty < 0) {
                $this->_productProblems[] = $product->getSku(); 
            }            
	    }
	}
	
    /**
     * get problem list
     *
     * @return array
     */
     public function getProductProblemList() {
         return $this->_productProblems;
         
     }

	protected function getProducts($apiOrder) {
		$out = array();
		foreach($apiOrder->order_items->item as $apiProduct) {
			if($apiProduct->is_delivery_item == 1) {
				//shipping cost
				//clear any keys that may exist:
				Mage::unregister(Modago_Integrator_Model_Shipping_Zolagoshipment::SHIPPING_COST_REGISTRY_KEY);
				Mage::unregister(Modago_Integrator_Model_Shipping_Zolagoshipment::SHIPPING_ACTIVE_REGISTRY_KEY);

				//register new ones
				Mage::register(Modago_Integrator_Model_Shipping_Zolagoshipment::SHIPPING_COST_REGISTRY_KEY,$apiProduct->item_value_after_discount);
				Mage::register(Modago_Integrator_Model_Shipping_Zolagoshipment::SHIPPING_ACTIVE_REGISTRY_KEY,true);
				continue;
			}

			$productId = $this->getProductIdBySku($apiProduct->item_sku);
			$this->_productStocks[] = $productId;
			if($productId) {
			    if (!Mage::helper('modagointegrator/api')->isSimpleOnly()) {
    				//check if product has configurable parent
    				$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productId);
                } else {
                    $parentIds = false;
                }
				if (is_array($parentIds) && count($parentIds)) {
					$childId = $productId;
					$productId = current($parentIds);
				} else {
					$childId = false;
				}                
				$product = $this->getProduct($productId);
				/*$taxPercent = $this->getProductTaxRate($product);
				$priceIncl = $apiProduct->item_value_after_discount; //brutto
				$price = round((($priceIncl / (100 + $taxPercent)) * 100),2); //netto*/

				$product->setPrice($apiProduct->item_value_after_discount); //$price
				$product->setSpecialPrice(null);

				$out[$productId] = array(
					'model' => $product,
					'qty' => $apiProduct->item_qty
				);

				if($childId) {
					//set super attributes on out product
					$superAttributes = $product
						->getTypeInstance(true)
						->getConfigurableAttributes($product);

					if(count($superAttributes)) {
						$superAttributeArray = array();
						foreach($superAttributes as $superAttribute) {
							$superAttributeId = $superAttribute->getAttributeId();
							$currentOptionId = Mage::getResourceModel('catalog/product')
								->getAttributeRawValue($childId,$superAttributeId,$this->_storeId);
							$superAttributeArray[$superAttributeId] = $currentOptionId;
						}
						$out[$productId]['super_attribute'] = $superAttributeArray;
					}
				}
			} else {
				throw Mage::exception('Modago_Integrator',
					Mage::helper('modagointegrator')->__('Cannot find product with SKU: %s (External order id: %s)',$apiProduct->item_sku,$this->_modagoOrderId)
				);
			}
		}
		return $out;
	}

	/**
	 * @param int $productId
	 * @return Mage_Catalog_Model_Product
	 */
	protected function getProduct($productId) {
		return Mage::getModel('catalog/product')->setStoreId($this->_storeId)->load($productId);
	}

	/**
	 * @param string $productSku
	 * @return int
	 */
	protected function getProductIdBySku($productSku) {
		/** @var Mage_Catalog_Model_Product $productModel */
		$productModel = Mage::getModel('catalog/product');
		return $productModel->getIdBySku($productSku);
	}

	/**
	 * @param string $productSku
	 * @return Mage_Catalog_Model_Product
	 */
	protected function getProductBySku($productSku) {
		return $this->getProduct($this->getProductIdBySku($productSku));
	}

	/**
	 * @return Mage_Core_Model_Store
	 */
	protected function getStore() {
		return Mage::app()->getStore($this->_storeId);
	}

	/**
	 * @return int
	 */
	protected function getStoreId() {
		return $this->_storeId;
	}

	/**
	 * @param string $apiShippingMethod
	 * @return string
	 */
	protected function getShippingMethod($apiShippingMethod) {
		return $this->getHelper()->getShippingMethodByApi($apiShippingMethod);
	}

	/**
	 * @param string $apiShippingMethod
	 * @return string
	 */
	protected function getShippingCarrierCode($apiShippingMethod) {
		return $this->getHelper()->getShippingCarrierByApi($apiShippingMethod);
	}

	/**
	 * @param string $apiPaymentMethod
	 * @return string
	 */
	protected function getPaymentMethod($apiPaymentMethod) {
		return $this->getHelper()->getPaymentMethodByApi($apiPaymentMethod);
	}
}
