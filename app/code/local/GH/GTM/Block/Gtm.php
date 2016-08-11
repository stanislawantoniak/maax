<?php

/**
 * Google Tag Manager Block
 *
 * Class GH_GTM_Block_Gtm
 *
 * @method $this setCustomData() add js to extend dataLayer object
 */
class GH_GTM_Block_Gtm extends Shopgo_GTM_Block_Gtm {
	/**
	 * GH_GTM_Block_Gtm constructor.
	 */
	public function _construct() {
		parent::_construct();
		$template = $this->getTemplate();
		if (empty($template)) {
			$this->setTemplate('ghgtm/default.phtml');
		}
	}

	protected function _getTransactionData()
	{
		/** @var Zolago_Dropship_Helper_Data $udropHlp */
		$udropHlp = Mage::helper('udropship');
		/** @var Zolago_Common_Helper_Data $zcHlp */
		$zcHlp = Mage::helper('zolagocommon');
		/** @var Zolago_Solrsearch_Helper_Data $solrHlp */
		$solrHlp = Mage::helper("zolagosolrsearch");
		$data = array();

		$orderIds = $this->getOrderIds();
		if (empty($orderIds) || !is_array($orderIds)) return array();

		$collection = Mage::getResourceModel('sales/order_collection')->addFieldToFilter('entity_id', array('in' => $orderIds));

		$i = 0;
		$products = array();

		foreach ($collection as $order) {
			/** @var Mage_Sales_Model_Order $order */
			if ($i == 0) {
				// Build all fields for first order.

				// Coupon name if applied
				$couponName = array();
				$ruleIds = array_unique(explode(',', $order->getAppliedRuleIds()));
				if (!empty($ruleIds)) {
					foreach ($ruleIds as $id) {
						$rule = Mage::getModel('salesrule/rule')->load($id);
						$couponName[] = $rule->getName();
					}
					if (!empty($couponName)) {
						$couponName = implode('|', $couponName);
					}
				}

				$data = array(
					'transactionId' => $order->getIncrementId(),
					'transactionDate' => date("Y-m-d"),
					'transactionType' => Mage::helper('gtm')->getTransactionType(),
					'transactionAffiliation' => Mage::helper('gtm')->getTransactionAffiliation(),
					'transactionTotal' => round($order->getBaseGrandTotal(),2),
					'transactionShipping' => round($order->getBaseShippingAmount(),2),
					'transactionTax' => round($order->getBaseTaxAmount(),2),
					'transactionPaymentType' => $order->getPayment()->getMethodInstance()->getTitle(),
					'transactionCurrency' => Mage::app()->getStore()->getBaseCurrencyCode(),
					'transactionShippingMethod' => $order->getShippingCarrier() ? $order->getShippingCarrier()->getCarrierCode() : 'No Shipping Method',
					'transactionPromoCode' => $order->getCouponCode(),
					'transactionPromoName' => $couponName,
					'transactionProducts' => array()
				);
			} else {
				// For subsequent orders, append to order ID, totals and shipping method.
				$data['transactionId'] .= '|' . $order->getIncrementId();
				$data['transactionTotal'] += $order->getBaseGrandTotal();
				$data['transactionShipping'] += $order->getBaseShippingAmount();
				$data['transactionTax'] += $order->getBaseTaxAmount();


				/** @var Mage_Checkout_Model_Session $checkoutSession */
				$checkoutSession = Mage::getSingleton('checkout/session');
				$checkoutData = $checkoutSession->getData();
				/** @var GH_GTM_Helper_Data $gtmHelper */
				$gtmHelper = Mage::helper("ghgtm");

				if(isset($checkoutData['shipping_method'])) {
					$shippingMethod = $gtmHelper->getShippingMethodName(current($checkoutData['shipping_method']));
					if (!empty($shippingMethod)) {
						$data['transactionShippingMethod'] = $shippingMethod;
					}
				}

				if(isset($checkoutData['payment']['method'])) {
					$paymentMethod = $gtmHelper->getPaymentMethodName($checkoutData['payment']['method']);
					if (!empty($paymentMethod)) {
						$data['transactionPaymentMethod'] = $paymentMethod;
					}
				}

				if(isset($checkoutData['payment']['additional_information']['provider'])) {
					$paymentDetails = $checkoutData['payment']['additional_information']['provider'];
					if (!empty($paymentDetails)) {
						$data['transactionPaymentDetails'] = $paymentDetails;
					}
				}
			}

			// Build products array.
			/** @var Mage_Sales_Model_Order_Item $item */
			foreach ($order->getAllVisibleItems() as $item) {
				$product = Mage::getModel("zolagocatalog/product")->load($item->getProductId());
				/** @var Zolago_Catalog_Model_Product $product */
				/** @var Zolago_Catalog_Model_Category $cat */
				$cat = $solrHlp->getDefaultCategory($product, Mage::app()->getStore()->getRootCategoryId());
				$productCategories = array();
				if (!empty($cat)) {
					// Only categories after root category
					$productCategories = array_slice($cat->getPathIds(),1 + array_search(Mage::app()->getStore()->getRootCategoryId(), $cat->getPathIds()));
				}

				$categories = array();
				foreach ($productCategories as $category) {
					$categories[] = trim(Mage::helper('core')->escapeHtml(Mage::getModel('catalog/category')->load($category)->getName()));
				}
				$vendor    = $udropHlp->getVendor($product->getUdropshipVendor())->getVendorName();
				$brandshop = $udropHlp->getVendor($product->getbrandshop())->getVendorName();
				if (empty($products[$item->getSku()])) {
					// Build all fields the first time we encounter this item.
					$products[$item->getSku()] = array(
						'name' => $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($item->getName())),
						'id' => $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($product->getSku())),
						'skuv' => $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($zcHlp->getSkuvFromSku($product->getSku(),$product->getUdropshipVendor()))),
						'simple_sku' => $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($item->getSku())),
						'simple_skuv' => $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($zcHlp->getSkuvFromSku($item->getSku(),$item->getUdropshipVendor()))),
						'category' => implode('/',$categories),
						'price' => (double)number_format($item->getbasePrice() - (($item->getDiscountAmount() - $item->getHiddenTaxAmount())/$item->getQtyOrdered()),2,'.',''),
						'quantity' => (int)$item->getQtyOrdered(),
						'vendor' => Mage::helper('core')->escapeHtml($vendor),
						'brandshop' => Mage::helper('core')->escapeHtml($brandshop),
						'brand' => Mage::helper('core')->escapeHtml($product->getAttributeText('manufacturer')),
					);
					// Add MSRP only if exist
					if ($msrp = (double)number_format($product->getMsrp(),2,'.','')) $products[$item->getSku()]['msrp_incl_tax'] = $msrp;

					$children = $item->getChildrenItems();
					if (!empty($children) && isset($children[0])) {
						$products[$item->getSku()]['variant'] = $children[0]->getProduct()->getAttributeText('size');
					}
				} else {
					// If we already have the item, update quantity.
					$products[$item->getSku()]['quantity'] += (int)$item->getQtyOrdered();
				}
			}

			$i++;
		}

		// Push products into main data array.
		foreach ($products as $product) {
			$data['transactionProducts'][] = $product;
		}

		// Trim empty fields from the final output.
		foreach ($data as $key => $value) {
			if (!is_numeric($value) && empty($value)) unset($data[$key]);
		}

		return $data;
	}

	/**
	 * Generate JavaScript for the data layer.
	 *
	 * @return string|null
	 */
	protected function _getDataLayer() {
		// Initialise our data source.
		$data = array();
		$dataScript = '';

		/** @var GH_GTM_Helper_Data $gtmHlp */
		$gtmHlp = Mage::helper('ghgtm');

		// Get transaction and visitor data.
		$data = $data + $this->_getTransactionData();
		$data = $data + $this->_getContextData();
		$data = $data + $gtmHlp->getVisitorData(false);

		// Get transaction and visitor data, if desired.
		if ($gtmHlp->isDataLayerEnabled() && !empty($data)) {
			// Generate the data layer JavaScript.
			$dataScript .= "<script>dataLayer = [" . json_encode($data) . "];</script>\n\n";
		}
		// removed Spying part
		return $dataScript;
	}

	/**
	 * @return array
	 */
	public function getRawDataLayer() {
		if (!Mage::helper('gtm')->isGTMAvailable()) {
			return '';
		}
		/** @var GH_GTM_Helper_Data $gtmHlp */
		$gtmHlp = Mage::helper('ghgtm');

		$data = $this->_getTransactionData();

		if ($gtmHlp->isDataLayerEnabled() && !empty($data)) {
			return json_encode($data);
		} else {
			return '';
		}
	}


	protected function _getContextData()
	{
		$data = array();

		//skip own stores
		/** @var Zolago_Common_Helper_Data $commonHlp */
		$commonHlp = Mage::helper('zolagocommon');
		if ($commonHlp->isOwnStore()) {
			return $data;
		}

		/** @var GH_GTM_Helper_Data $gtmHlp */
		$gtmHlp = Mage::helper("ghgtm");
		$path = $gtmHlp->getContextPath();
		$allowedPaths = $gtmHlp->getAllowedContextPaths();

		if(in_array($path,$allowedPaths)) {
			/** @var Mage_Core_Helper_Url $urlHlp */
			$urlHlp = Mage::helper('core/url');
			$urlData = parse_url($urlHlp->getCurrentUrl());
			$urlPath = explode("/",$urlData['path']);

			if(isset($urlPath[1]) && $urlPath[1]) {
				$vendorKey = $urlPath[1];
				$vendorData = $gtmHlp->getVendorDataByUrlKey($vendorKey);

				if(count($vendorData) && isset($vendorData['vendor_name']) && isset($vendorData['vendor_type'])) {
					switch($vendorData['vendor_type']) {
						case Zolago_Dropship_Model_Vendor::VENDOR_TYPE_BRANDSHOP:
							$data['contextType'] = 'brandshop';
							break;
						case Zolago_Dropship_Model_Vendor::VENDOR_TYPE_STANDARD:
							$data['contextType'] = 'vendor';
							break;
					}
					$data['contextDetails'] = $vendorData['vendor_name'];
				}
			}

			if(!isset($data['contextType'])) {
				$data['contextType'] = 'general';
			}
		}
		return $data;
	}
}