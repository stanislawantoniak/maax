<?php

class Orba_Common_Ajax_CustomerController extends Orba_Common_Controller_Ajax {
	
	const MAX_CART_ITEMS_COUNT = 5;

    public function get_account_informationAction()
    {
		
		//$profiler = Mage::helper('zolagocommon/profiler');
		/* @var $profiler Zolago_Common_Helper_Profiler */
		
		//$profiler->start();
        
        /*shipping_cost*/
		
        $cost = Mage::helper('zolagomodago/checkout')->getShippingCostSummary();
		//$profiler->log("Quote shipping costs");

        $costSum = 0;
        if (!empty($cost)) {
            $costSum = array_sum($cost);
        }
        $formattedCost = Mage::helper('core')->currency($costSum, true, false);
        /*shipping_cost*/

		$quote = Mage::helper('checkout/cart')->getQuote();
		/* @var $quote Mage_Sales_Model_Quote */
		$totals = $quote->getTotals();
		//$profiler->log("Quote totals");

	    /** @var Mage_Persistent_Helper_Session $persistentHelper */
	    $persistentHelper = Mage::helper('persistent/session');

	    /** @var Zolago_Customer_Model_Session $customerSession */
		$customerSession = Mage::getSingleton('customer/session');

		$persistent = $persistentHelper->isPersistent() &&
			!$customerSession->isLoggedIn();
		
		//$profiler->log("Persistent");
	    //set registry to correctly identify current context
	    $ajaxRefererUrlKey = 'ajax_referer_url';
	    if(Mage::registry($ajaxRefererUrlKey)) {
		    Mage::unregister($ajaxRefererUrlKey);
	    }
	    Mage::register($ajaxRefererUrlKey,$this->_getRefererUrl());


	    /** @var Zolago_Solrsearch_Helper_Data $searchHelper */
		$searchHelper = Mage::helper('zolagosolrsearch');
		$searchContext = $searchHelper->getContextSelectorArray(
				$this->getRequest()->getParams()
		);
		//$profiler->log("Context");

        $layout = $this->getLayout();
        $content = array(
            'user_account_url' => Mage::getUrl('customer/account', array("_no_vendor"=>true)),
            'user_account_url_orders' => Mage::getUrl('sales/order/process', array("_no_vendor"=>true)),
            'logged_in' => Mage::helper('customer')->isLoggedIn(),
            'favorites_count' => $this->_getFavorites(),
            'favorites_url' => Mage::getUrl("wishlist", array("_no_vendor"=>true)),
            'cart' => array(
                'all_products_count' => Mage::helper('checkout/cart')->getSummaryCount(),
                'products' => $this->_getShoppingCartProducts(),
                'total_amount' => round(isset($totals["subtotal"]) ? $totals["subtotal"]->getValue() : 0, 2),
                'shipping_cost' => $formattedCost,
                'show_cart_url' => Mage::getUrl('checkout/cart', array("_no_vendor"=>true)),
                'currency_code' => Mage::app()->getStore()->getCurrentCurrencyCode(),
                'currency_symbol' => Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol()
            ),
			'persistent' => $persistent,
			'persistent_url' => Mage::getUrl("persistent/index/forget", array("_no_vendor"=>true)),
			'search' => $searchContext,
            'salesmanago_tracking' => $this->_cleanUpHtml($layout->createBlock("tracking/layer")->toHtml())
        );
		//$profiler->log("Rest");
		
		// Load product context data & crosssell wishlist info & ask vendor form customer info
		if($productId=$this->getRequest()->getParam("product_id")){
			$product = Mage::getModel("catalog/product");
			/* @var $product Mage_Catalog_Model_Product */
			$product->setId($productId);

			// Load wishlist count
			$wishlistCount = $product->getResource()->getAttributeRawValue(
					$productId, "wishlist_count", Mage::app()->getStore()->getId());
			
			$content['product'] = array(
				"entity_id"=>$productId,
				"in_my_wishlist" => Mage::helper('zolagowishlist')->productBelongsToMyWishlist($product),
				"wishlist_count" => (int)$wishlistCount
			);

            // Varnish cache html crosssell products
            // so info about that products is in wishlist need to be updated
            $crosssellProducts  = $this->getRequest()->getParam("crosssell_ids");
            if (!empty($crosssellProducts)) {

                $productsInWishList = Mage::helper('zolagowishlist')->checkProductsBelongsToMyWishlist($crosssellProducts);
                $cs = array();
                foreach ($productsInWishList as $prod) {
                    $id = $prod->getProduct()->getData('entity_id');
                    $cs[$id]['in_my_wishlist'] = 1;
                    $cs[$id]['entity_id']      = $id;
                }
                $content['crosssell'] = array_values($cs);
            }

            // Customer info for contact form in product page
            if (Mage::helper('customer')->isLoggedIn()) {
                /* @var $coreHelper Mage_Core_Helper_Data */
                $coreHelper = Mage::helper('core');
                /** @var Mage_Customer_Model_Session $session */
                $session = Mage::getSingleton('customer/session');

                $content['customer_name']  = $coreHelper->escapeHtml($session->getCustomer()->getName());
                $content['customer_email'] = $coreHelper->escapeHtml($session->getCustomer()->getEmail());
            }
            // And populate data question form if error
            $dataPopulate = Mage::getSingleton('udqa/session')->getDataPopulate(true);
            if ($dataPopulate) {
                $content['data_populate']['customer_name']  = isset($dataPopulate['customer_name'])  ? $dataPopulate['customer_name']  : false;
                $content['data_populate']['customer_email'] = isset($dataPopulate['customer_email']) ? $dataPopulate['customer_email'] : false;
                $content['data_populate']['question_text']  = isset($dataPopulate['question_text'])  ? $dataPopulate['question_text']  : false;
            }
		}



	    if($this->getRequest()->getParam('recently_viewed')) {
            /** @var Mage_Reports_Block_Product_Viewed $singleton */
            $singleton = Mage::getSingleton('Mage_Reports_Block_Product_Viewed');

            // By persistent
            if($persistentHelper->isPersistent() && $persistentHelper->getSession()->getCustomerId()){
                $customerId = $persistentHelper->getSession()->getCustomerId();
                $singleton->setCustomerId($customerId);
            }
		    $recentlyViewedProducts = $singleton->getItemsCollection();

		    $recentlyViewedContent = array();
		    if ($recentlyViewedProducts->count() > 0) {

			    foreach ($recentlyViewedProducts as $product) {
                    if ($product->getId() == $productId) {
                        // Don't show in last viewed box current product
                        continue;
                    }
				    /* @var $product Zolago_Catalog_Model_Product */
				    $image = Mage::helper("zolago_image")
					    ->init($product, 'small_image')
					    ->setCropPosition(Zolago_Image_Model_Catalog_Product_Image::POSITION_CENTER)
					    ->adaptiveResize(200, 312);
				    $recentlyViewedContent[] = array(
					    'title' => Mage::helper('catalog/output')->productAttribute($product, $product->getName(), 'name'),
					    'image_url' => (string)$image,
					    'redirect_url' => $product->getNoVendorContextUrl()
				    );

			    }

			    $content['recentlyViewed'] = $recentlyViewedContent;
		    }
	    }

        /*
         * When Varnish ON we need to add info about last viewed product
         * for event @see app/code/local/Zolago/Reports/Model/Event/Observer.php::ajaxAddLastViewed
         */
        if ($productId) {
            /* @var $product Mage_Catalog_Model_Product */
            $product = Mage::getModel("catalog/product");
            $product->setId($productId);
            Mage::dispatchEvent('ajax_get_account_information_after', array('product' => $product));
        }

	    /* salesmanago cookie */
	    if($customerSession->isLoggedIn()) {
		    $customer = $customerSession->getCustomer();
	    } elseif($persistentHelper->isPersistent() && $persistentHelper->getSession()->getCustomerId()) {
			$customer = $persistentHelper->getCustomer();
	    } else {
		    $customer = false;
	    }

	    if($customer !== false && $customer->getId()) {
		    /** @var Zolago_SalesManago_Helper_Data $salesmanagoHelper */
		    $salesmanagoHelper = Mage::helper('tracking');

		    $smContactId = false;

		    if($customer->getSalesmanagoContactId()) { //if customer has salesmanago contact id then set it to variable
			    $smContactId = $customer->getSalesmanagoContactId();
		    } else {
		        //sync customer with salesmanago as contact - it updates existing one or generates a new one
			    try {
				    $data = $salesmanagoHelper->_setCustomerData($customer->getData());
				    $r = $salesmanagoHelper->salesmanagoContactSync($data, true);
				    $smContactId = isset($r['contactId']) && $r['contactId'] ? $r['contactId'] : false;
			    } catch (Exception $e) {
				    Mage::logException($e);
			    }

			    if($smContactId) {
				    $customer->setData('salesmanago_contact_id', $smContactId)
					    ->getResource()
					    ->saveAttribute($customer, "salesmanago_contact_id");
			    }
		    }

		    if(!isset($_COOKIE['smclient']) || empty($_COOKIE['smclient']) ||
			    (isset($_COOKIE['smclient']) && $_COOKIE['smclient'] != $smContactId)) {
			    $salesmanagoHelper->addCookie('smclient',$smContactId);
		    }
	    }


	    if(Mage::getStoreConfig(Shopgo_GTM_Helper_Data::XML_PATH_ACTIVE)) {
		    /** @var GH_GTM_Helper_Data $gtmHelper */
		    $gtmHelper = Mage::helper('gh_gtm');
		    $content['visitor_data'] = $gtmHelper->getVisitorData();
	    }


        $result = $this->_formatSuccessContentForResponse($content);
        $this->_setSuccessResponse($result);
    }
	
	public function _getShoppingCartProducts(){
		
		$quote = Mage::getSingleton('checkout/session')->getQuote();
        $cartItems = $quote->getAllVisibleItems();
		
		if(sizeof($cartItems) > self::MAX_CART_ITEMS_COUNT){
			$cartItems = array_slice($cartItems, 0, self::MAX_CART_ITEMS_COUNT);	
		}
		
//		// Product load 
//		$productsIds = array();
//		foreach ($cartItems as $item){
//            $productsIds[] = $item->getProductId();
//		}
//		
//		$collection = Mage::getResourceModel("catalog/product_collection");
//		/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
//		
//		$collection->addAttributeToSelect("name");
//		$collection->addAttributeToSelect("image");
//		$collection->addAttributeToSelect("url_path");
//		
//		if($productsIds){
//			$collection->addIdFilter($productsIds);
//		}else{
//			$collection->addIdFilter(-1);
//		}
		
		$array = array();
        foreach ($cartItems as $item)
        {
//            $productId = $item->getProductId();
//            $product = $collection->getItemById($productId);
            $product = $item->getProduct();
			/* @var $product Mage_Catalog_Model_Product */
			
			if($product && $product->getId()){
				$options = $this->_getProductOptions($item);
				$image = Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(40, 50);

				$array[] = array(
					'name' => $product->getName(),
					'url' => $product->getNoVendorContextUrl(),
					'qty' => $item->getQty(),
					'unit_price' => round($item->getPriceInclTax(), 2),
					'image_url' => (string) $image,
					'options' => $options
				);
			}
			
        }
		
		return (sizeof($array) > 0) ? $array : 0;
	}
	
	public function _getFavorites(){
		$wishlist = Mage::helper('zolagowishlist')->getWishlist();
		return $wishlist->getItemsCount();
	}
	
	public function _getProductOptions($item){
		
		$product = $item->getProduct();
		
		$options = $product->getTypeInstance(true)->getOrderOptions($product);
		
		$array = array();
		if ($options){
			if(isset($options['attributes_info'])){
				foreach ($options['attributes_info'] as $attrib){
					
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
     * clean ups html from excess of newlines, whitespaces and tabs
     * @param $string
     * @return string
     */
    protected function _cleanUpHtml($string) {
        $string = preg_replace('/\s*$^\s*/m', "\n", $string);
        return preg_replace('/[ \t]+/', ' ', $string);
    }
}