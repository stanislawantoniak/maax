<?php

class Orba_Common_Ajax_CustomerController extends Orba_Common_Controller_Ajax {

	/**
	 * Set Sales Manago cookie with their ID
	 */
	public function get_salesmanago_informationAction() {
		/** @var Zolago_Customer_Model_Session $customerSession */
		$customerSession = Mage::getSingleton('customer/session');
		/** @var Mage_Persistent_Helper_Session $persistentHelper */
		$persistentHelper = Mage::helper('persistent/session');

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
	}

    public function get_account_informationAction()
    {
		/** @var Orba_Common_Helper_Ajax_Customer_Cache $cacheHelper */
		$cacheHelper = Mage::helper('orbacommon/ajax_customer_cache');

		/* @var $profiler Zolago_Common_Helper_Profiler */
		$profiler = Mage::helper('zolagocommon/profiler');
		$profiler->start();

		$this->saveIsAjaxContext();
		$cart = $cacheHelper->getCart();
		$profiler->log("cart");
		$search = $cacheHelper->getSearch($this->getRequest()->getParams());
		$profiler->log("search");
		$loggedIn = Mage::helper('customer')->isLoggedIn();
		$profiler->log("logged in");
		$favorites = $this->_getFavorites();
		$profiler->log("favs");
		$isUserPersistent = $this->isUserPersistent();
		$profiler->log("persistent");

        $content = array(
			'logged_in'			=> $loggedIn,
			'favorites_count'	=> $favorites,
			'cart'				=> $cart,
			'persistent'		=> $isUserPersistent,
			'search'			=> $search,
        );
		$profiler->log("content");
		
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
			/**
			 * When Varnish ON we need to add info about last viewed product
			 * for event @see app/code/local/Zolago/Reports/Model/Event/Observer.php::ajaxAddLastViewed
			 */
			Mage::dispatchEvent('ajax_get_account_information_after', array('product' => $product));
		}

		$profiler->log("product");

	    if($this->getRequest()->getParam('recently_viewed')) {
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
		$profiler->log("recentlyViewed");


		/** @var GH_GTM_Helper_Data $gtmHelper */
		$gtmHelper = Mage::helper('ghgtm');
	    if($gtmHelper->isGTMAvailable()) {
		    $content['visitor_data'] = $gtmHelper->getVisitorData();
	    }
		$profiler->log("gtm");

        $result = $this->_formatSuccessContentForResponse($content);
        $this->_setSuccessResponse($result);

		$profiler->log("all");
		$profiler->log("--------------------------------");
    }

	public function _getFavorites(){
		$wishlist = Mage::helper('zolagowishlist')->getWishlist();
		return $wishlist->getItemsCount();
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

	public function isUserPersistent() {
		/** @var Mage_Persistent_Helper_Session $persistentHelper */
		$persistentHelper = Mage::helper('persistent/session');
		/** @var Zolago_Customer_Model_Session $customerSession */
		$customerSession = Mage::getSingleton('customer/session');
		return $persistentHelper->isPersistent() && !$customerSession->isLoggedIn();
	}

	/**
	 * Need to store in registry info about that is ajax context
	 * Purpose: for correct receiving data like current vendor
	 *
	 * @return $this
	 */
	public function saveIsAjaxContext() {
		//set registry to correctly identify current context
		$ajaxReferrerUrlKey = 'ajax_referrer_url';
		if (Mage::registry($ajaxReferrerUrlKey)) {
			Mage::unregister($ajaxReferrerUrlKey);
		}
		Mage::register($ajaxReferrerUrlKey, $this->_getRefererUrl());
		return $this;
	}
}