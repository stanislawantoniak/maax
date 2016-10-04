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
		// Available params
		$productId = $this->getRequest()->getParam("product_id");
		$crosssellIds = $this->getRequest()->getParam("crosssell_ids");
		$recentlyViewed = (bool)$this->getRequest()->getParam("recently_viewed");
//		$categoryId = $this->getRequest()->getParam("category_id");

	    $utmData = $this->getRequest()->getParam('utm_data');
	    if($utmData) {
		    /** @var GH_UTM_Helper_Data $utmHelper */
		    $utmHelper = Mage::helper('ghutm');
		    $utmHelper->updateUtmData($utmData);
	    }

		/** @var Orba_Common_Helper_Ajax_Customer_Cache $cacheHelper */
		$cacheHelper = Mage::helper('orbacommon/ajax_customer_cache');

		$favsProdsIds = $cacheHelper->getFavoritesProductsIds();

        $content = array(
			'logged_in'			=> Mage::helper('customer')->isLoggedIn(),
			'favorites_count'	=> $cacheHelper->getFavoritesCount(),
			'cart'				=> $cacheHelper->getCart(),
			'persistent'		=> $this->isUserPersistent(),
			'search'			=> $cacheHelper->getSearch($this->getRequest()->getParams()),
        );

		// Add info about recently viewed products only if needed
		if ($recentlyViewed) {
			$content['recently_viewed'] = $cacheHelper->getRecentlyViewed($productId);
		}

		// Customer info for contact form in product page
		if ($productId && Mage::helper('customer')->isLoggedIn()) {
			$content['customer_name']  = $cacheHelper->getCustomerName();
			$content['customer_email'] = $cacheHelper->getCustomerEmail();
		}
		// And populate data question form if error
		if ($productId && $dataPopulate = Mage::getSingleton('udqa/session')->getDataPopulate(true)) {
			$content['data_populate']['customer_name'] = isset($dataPopulate['customer_name']) ? $dataPopulate['customer_name'] : false;
			$content['data_populate']['customer_email'] = isset($dataPopulate['customer_email']) ? $dataPopulate['customer_email'] : false;
			$content['data_populate']['question_text'] = isset($dataPopulate['question_text']) ? $dataPopulate['question_text'] : false;
		}

		// Varnish cache html cross sell products
		// so info about that products is in wishlist need to be updated
		if ($productId && !empty($crosssellIds)) {
			foreach ($favsProdsIds as $id => $value) {
				$content['crosssell'][] = array(
					'in_my_wishlist'	=> $value,
					'entity_id'			=> $id
				);
			}
		}

		// Load product context data & crosssell wishlist info
		if($productId){
			$product = Mage::getModel("catalog/product");
			/* @var $product Mage_Catalog_Model_Product */
			$product->setId($productId);

			// Load wishlist count
			$wishlistCount = (int)$product->getResource()->getAttributeRawValue(
					$productId, "wishlist_count", Mage::app()->getStore()->getId());

			$content['product'] = array(
				"entity_id"			=> $productId,
				"in_my_wishlist"	=> isset($favsProdsIds[$productId]) ? 1 : 0,
				"wishlist_count"	=> $wishlistCount
			);
			/**
			 * When Varnish ON we need to add info about last viewed product
			 * for event @see app/code/local/Zolago/Reports/Model/Event/Observer.php::ajaxAddLastViewed
			 */
			Mage::dispatchEvent('ajax_get_account_information_after', array('product' => $product));
		}

		/** @var GH_GTM_Helper_Data $gtmHelper */
		$gtmHelper = Mage::helper('ghgtm');
	    if($gtmHelper->isGTMAvailable()) {
		    $content['visitor_data'] = $gtmHelper->getVisitorData();
		    if(isset($content['visitor_data'][GH_UTM_Model_Source::GHUTM_DATE_NAME])) {
			    $content['visitor_data'][GH_UTM_Model_Source::GHUTM_DATE_NAME."_date"] = date('Y-m-d H:i:s',$content['visitor_data'][GH_UTM_Model_Source::GHUTM_DATE_NAME]);
		    }
	    }

		$cacheHelper->saveCustomerInfoCache();
        $result = $this->_formatSuccessContentForResponse($content);
        $this->_setSuccessResponse($result);
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

}