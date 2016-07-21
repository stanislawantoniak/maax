<?php
class Zolago_Checkout_Helper_Data extends Mage_Core_Helper_Abstract {

	protected $inpostLocker = null;
	protected $pickUpPoint = null;

	protected $_deliveryMethodCode = null;


	public function getSelectedShipping(){
		$checkoutSession = Mage::getSingleton('checkout/session');

		//1. Get shipping from session
		$shipping_method_session = $checkoutSession->getData("shipping_method");
		$deliveryPointName = $checkoutSession->getData("delivery_point_name");

		if(!empty($shipping_method_session)){
			return array(
				"source" => "session",
				"methods" => $shipping_method_session,
				"shipping_point_code" => $deliveryPointName
			);
		}


		//2. Get shipping from quote
		$address = $checkoutSession->getQuote()->getShippingAddress();
		$details = $address->getUdropshipShippingDetails();
		$details = $details ? Zend_Json::decode($details) : array();
		$deliveryPointName = $address->getData("delivery_point_name");
		$methods = array();
		if(!empty($details) && isset($details["methods"])){
			foreach($details["methods"] as $vendorId => $methodData){
				$methods[$vendorId] = $methodData["code"];
			}
			$checkoutSession->setData("delivery_point_name",$deliveryPointName);
			$checkoutSession->setShippingMethod($methods);
			return array(
				"source" => "quota",
				"methods" => $methods,
				"shipping_point_code" => $deliveryPointName
			);
		}
		return array(
			"methods" => array(),
			"shipping_point_code" => ""
		);

	}



	public function getDeliveryPointShippingAddress(){

		/** @var Zolago_Checkout_Helper_Data $helper */
		$helper = Mage::helper("zolagocheckout");

		$deliveryMethodData = $helper->getMethodCodeByDeliveryType();
		$deliveryMethodCode = $deliveryMethodData->getDeliveryCode();

		$shippingAddressFromDeliveryPoint = array();

		switch ($deliveryMethodCode) {
			case 'zolagopickuppoint':
				/* @var $pos  Zolago_Pos_Model_Pos */
				$pos = $helper->getPickUpPoint();
				$shippingAddressFromDeliveryPoint = $pos->getShippingAddress();
				break;
			case 'ghinpost':
				/* @var $locker GH_Inpost_Model_Locker */
				$locker = $helper->getInpostLocker();
				$shippingAddressFromDeliveryPoint = $locker->getShippingAddress();
				break;
		}

		return $shippingAddressFromDeliveryPoint;
	}

	/**
	 * Retrieve Pick-Up Point object for current checkout session
	 *
	 * @return Zolago_Pos_Model_Pos
	 */
	public function getPickUpPoint() {
		if (is_null($this->pickUpPoint)) {

			$selectedShipping = $this->getSelectedShipping();
			$deliveryPointName = $selectedShipping['shipping_point_code'];

			/* @var $pos  Zolago_Pos_Model_Pos */
			$pos = Mage::getModel("zolagopos/pos")->load($deliveryPointName);

			if (!$pos->getIsAvailableAsPickupPoint()) {
				$pos = Mage::getModel("zolagopos/pos");
			}
			$this->pickUpPoint = $pos;
		}
		return $this->pickUpPoint;
	}

	/**
	 * Retrieve InPost Locker object for current checkout session
	 * 
	 * @return GH_Inpost_Model_Locker
	 */
	public function getInpostLocker() {
		if (is_null($this->inpostLocker)) {

			$selectedShipping = $this->getSelectedShipping();
			$deliveryPointName = $selectedShipping['shipping_point_code'];

			/** @var GH_Inpost_Model_Locker $locker */
			$locker = Mage::getModel("ghinpost/locker");
			$locker->loadByLockerName($deliveryPointName);
			if (!$locker->getIsActive()) {
				$locker = Mage::getModel("ghinpost/locker");
			}
			$this->inpostLocker = $locker;
		}
		return $this->inpostLocker;
	}
	
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

			$data = array('products' => array());
			/** @var Mage_Sales_Model_Quote_Item $item */
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
					'price' => (double)number_format($item->getbasePrice() - (($item->getDiscountAmount() - $item->getHiddenTaxAmount())/$item->getQty()), 2, '.', ''),
					'quantity' => (int)$item->getQty(),
					'vendor' => Mage::helper('core')->escapeHtml($vendor),
					'brandshop' => Mage::helper('core')->escapeHtml($brandshop),
					'brand' => Mage::helper('core')->escapeHtml($product->getAttributeText('manufacturer')),
				);
				// Add MSRP only if exist
				if ($msrp = (double)number_format($product->getMsrp(),2,'.','')) $_product['msrp_incl_tax'] = $msrp;

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
			$data['basket_currency'] = $quota->getQuoteCurrencyCode();
			$data['basket_total'] = $quota->getBaseGrandTotal();
			// Coupon name if applied
			$couponName = array();
			$ruleIds = array_unique(explode(',', $quota->getAppliedRuleIds()));
			if (!empty($ruleIds)) {
				foreach ($ruleIds as $id) {
					$rule = Mage::getModel('salesrule/rule')->load($id);
					$couponName[] = $rule->getName();
				}
				if (!empty($couponName)) {
					$data['basket_coupon'] = implode('|', $couponName);
				}
			}

			/** @var Mage_Checkout_Model_Session $checkoutSession */
			$checkoutSession = Mage::getSingleton('checkout/session');
			$checkoutData = $checkoutSession->getData();
			/** @var GH_GTM_Helper_Data $gtmHelper */
			$gtmHelper = Mage::helper("ghgtm");

			if(isset($checkoutData['shipping_method'])) {
				$shippingMethod = $gtmHelper->getShippingMethodName(current($checkoutData['shipping_method']));
				if (!empty($shippingMethod)) {
					$data['basket_shipping_method'] = $shippingMethod;
				}
			}

			// Last used payment method and details
			$paymentMethod  = null;
			$paymentDetails = null;
			if ($quota->getCustomer() && $quota->getCustomer()->getId()) {
				$payment = $quota->getCustomer()->getLastUsedPayment();
				if (isset($payment['method'])) {
					$paymentMethod = $gtmHelper->getPaymentMethodName($payment['method']);
				}
				if (isset($payment['additional_information']['provider'])) {
					$paymentDetails = $payment['additional_information']['provider'];
				}
			}
			// Maybe new chosen
			if(isset($checkoutData['payment']['method'])) {
				$paymentMethod = $gtmHelper->getPaymentMethodName($checkoutData['payment']['method']);
			}
			if(isset($checkoutData['payment']['additional_information']['provider'])) {
				$paymentDetails = $checkoutData['payment']['additional_information']['provider'];
			}

			// Save payment method and details if exists
			if (!empty($paymentMethod)) {
				$data['basket_payment_method'] = $paymentMethod;
			}
			if (!empty($paymentDetails)) {
				$data['basket_payment_details'] = $paymentDetails;
			}
		}
		return $data;
	}


	/**
	 * @param bool $includeTitle
	 * @return mixed
	 */
	public function getMethodCodeByDeliveryType($includeTitle = false){

		$sessionShippingMethod = "";
		$selectedShipping = $this->getSelectedShipping();
		foreach($selectedShipping["methods"] as $vid => $methodSelectedData){
			$sessionShippingMethod = $methodSelectedData;
		}
		//$sessionShippingMethod (something like udtiership_4)
		$storeId = Mage::app()->getStore()->getStoreId();

		if (is_null($this->_deliveryMethodCode)) {
			$collection = Mage::getModel("udropship/shipping")->getCollection();
			$collection->getSelect()
				->join(
					array('udropship_shipping_method' => $collection->getTable('udropship/shipping_method')),
					"main_table.shipping_id = udropship_shipping_method.shipping_id",
					array(
						'udropship_method' => new Zend_Db_Expr('CONCAT_WS(\'_\',    udropship_shipping_method.carrier_code ,udropship_shipping_method.method_code)'),
					)
				);
			$collection->getSelect()->join(
				array('udtiership_delivery_type' => $collection->getTable('udtiership/delivery_type')),
				"udropship_shipping_method.method_code = udtiership_delivery_type.delivery_type_id",
				array("delivery_code")
			);

			if($includeTitle){
				$collection->getSelect()->joinLeft(
					array('udropship_shipping_title_default' => $collection->getTable('udropship/shipping_title')),
					"main_table.shipping_id = udropship_shipping_title_default.shipping_id AND udropship_shipping_title_default.store_id=0",
					array(
						"udropship_method_title" => "IF(udropship_shipping_title_store.title IS NOT NULL, udropship_shipping_title_store.title, udropship_shipping_title_default.title)"
					)
				);
				$collection->getSelect()->joinLeft(
					array('udropship_shipping_title_store' => $collection->getTable('udropship/shipping_title')),
					"main_table.shipping_id = udropship_shipping_title_store.shipping_id AND udropship_shipping_title_store.store_id={$storeId}",
					array()
				);
			}

			$collection->getSelect()->having("udropship_method=?", $sessionShippingMethod);

			$this->_deliveryMethodCode = $collection->getFirstItem();
		}


		return $this->_deliveryMethodCode;
	}
}