<?php
class Zolago_Checkout_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getPaymentFromSession() {
	    return Mage::getSingleton('checkout/session')->getPayment();
    }

    public function fixCartShippingRates() {
        $cost = array();

        $q = Mage::getSingleton('checkout/cart')->getQuote();
        $totalItemsInCart = Mage::helper('checkout/cart')->getItemsCount();

        /*shipping_cost*/
        if($totalItemsInCart > 0) {
            $a = $q->getShippingAddress();

            $qRates = $a->getGroupedAllShippingRates();

            /**
             * Fix rate quote query
             */
            if (!$qRates) {
                $a->setCountryId(Mage::app()->getStore()->getConfig("general/country/default"));
                $a->setCollectShippingRates(true);
                $a->collectShippingRates();
                $qRates = $a->getGroupedAllShippingRates();
            }
        }
    }

	/**
	 * Retrieve data about basket and products
	 *
	 * @return array
	 */
	public function getBasketDataLayer() {
		/** @var GH_GTM_Helper_Data $gtmHelper */
		$gtmHelper = Mage::helper('gtm');
		if (!$gtmHelper->isGTMAvailable()) {
			return array();
		}
		/** @var Zolago_Dropship_Helper_Data $udropHlp */
		$udropHlp = Mage::helper('udropship');
		/** @var Zolago_Common_Helper_Data $zcHlp */
		$zcHlp = Mage::helper('zolagocommon');
		/** @var Zolago_Solrsearch_Helper_Data $solrHlp */
		$solrHlp = Mage::helper("zolagosolrsearch");
		$data = array();

		/** @var Zolago_Sales_Model_Quote $quota */
		$quota = Mage::getSingleton('checkout/cart')->getQuote();
		$items = $quota->getAllVisibleItems();

		if ($gtmHelper->isDataLayerEnabled() && !empty($items)) {

			$data = array('products' => array(), 'basket' => array());
			/** @var  $item */
			foreach ($items as $item) {
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
				$_product = array(
					'name' => Mage::helper('core')->escapeHtml($item->getName()),
					'id' => Mage::helper('core')->escapeHtml($product->getSku()),
					'skuv' => $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($zcHlp->getSkuvFromSku($product->getSku(),$product->getUdropshipVendor()))),
					'simple_sku' => $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($item->getSku())),
					'simple_skuv' => $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($zcHlp->getSkuvFromSku($item->getSku(),$item->getUdropshipVendor()))),
					'category' => implode('/', $categories),
					'price' => (double)number_format($item->getbasePrice() - ($item->getDiscountAmount() - $item->getDiscountTaxCompensation()), 2, '.', ''),
					'quantity' => (int)$item->getQty(),
					'vendor' => Mage::helper('core')->escapeHtml($vendor),
					'brandshop' => Mage::helper('core')->escapeHtml($brandshop),
					'brand' => Mage::helper('core')->escapeHtml($product->getAttributeText('manufacturer')),
				);
				$children = $item->getChildren();
				if (!empty($children) && isset($children[0])) {
					$_product['variant'] = $children[0]->getProduct()->getAttributeText('size');
				}

				$data['products'][] = $_product;
			}
			if (empty($data['products'])) {
				// only if any products
				unset($data['products']);
			}

			// Info about basket
			$data['basket']['currency'] = $quota->getQuoteCurrencyCode();
			$data['basket']['total'] = $quota->getBaseGrandTotal();
			// Coupon name if applied
			$couponName = array();
			$ruleIds = array_unique(explode(',', $quota->getAppliedRuleIds()));
			if (!empty($ruleIds)) {
				foreach ($ruleIds as $id) {
					$rule = Mage::getModel('salesrule/rule')->load($id);
					$couponName[] = $rule->getName();
				}
				if (!empty($couponName)) {
					$data['basket']['coupon'] = implode('|', $couponName);
				}
			}

			/** @var Mage_Checkout_Model_Session $checkoutSession */
			$checkoutSession = Mage::getSingleton('checkout/session');
			$checkoutData = $checkoutSession->getData();
			/** @var GH_GTM_Helper_Data $gtmHelper */
			$gtmHelper = Mage::helper("gh_gtm");

			if(isset($checkoutData['shipping_method'])) {
				$shippingMethod = $gtmHelper->getShippingMethodName(current($checkoutData['shipping_method']));
				if (!empty($shippingMethod)) {
					$data['basket']['shipping_method'] = $shippingMethod;
				}
			}

			if(isset($checkoutData['payment']['method'])) {
				$paymentMethod = $gtmHelper->getPaymentMethodName($checkoutData['payment']['method']);
				if (!empty($paymentMethod)) {
					$data['basket']['payment_method'] = $paymentMethod;
				}
			}

			if(isset($checkoutData['payment']['additional_information']['provider'])) {
				$paymentDetails = $checkoutData['payment']['additional_information']['provider'];
				if (!empty($paymentDetails)) {
					$data['basket']['payment_details'] = $paymentDetails;
				}
			}
		}
		return $data;
	}
}