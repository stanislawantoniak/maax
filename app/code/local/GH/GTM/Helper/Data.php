<?php

/**
 * Class GH_GTM_Helper_Data
 */
class GH_GTM_Helper_Data extends Shopgo_GTM_Helper_Data {

	public function getVisitorData($includeEvent = true){
		$data = array();
		/** @var Zolago_Customer_Model_Session $customerSession */
		$customerSession = Mage::getSingleton('customer/session');

		// visitorHasSubscribed - by default 'no'
		$data['visitorHasSubscribed'] = 'no';

		// visitorId, visitorHasAccount
		$customerId = $customerSession->getCustomerId();
		if($customerId) {
			$data['visitorId'] = (string)$customerId;
			$data['visitorHasAccount'] = 'yes';

			//visitorHasSubscribed
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');

			$query =
				'SELECT `subscriber_status` FROM `' .
				$resource->getTableName('newsletter/subscriber') .
				'` WHERE `customer_id` = ' . $customerId;

			$result = $readConnection->fetchAll($query);
			if(count($result)) {
				$newsletterStatus = current(current($result));
				switch($newsletterStatus) {
					case Zolago_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED:
						$data['visitorHasSubscribed'] = 'yes';
						break;
					case Zolago_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED:
						$data['visitorHasSubscribed'] = 'unsubscribed';
						break;
				}
			}
		} else {
			$data['visitorHasAccount'] = 'no';
		}

		//visitorLogged
		$data['visitorLogged'] = $customerSession->isLoggedIn() ? 'yes' : 'no';

		if($includeEvent) {
			$data['event'] = 'visitorDataReady';
		}

		return $data;
	}

	const CONTEXT_PATH_SEARCH = "search/index/index";
	const CONTEXT_PATH_PRODUCT = "catalog/product/view";
	const CONTEXT_PATH_CATEGORY = "catalog/category/view";
	const CONTEXT_PATH_LP = "umicrosite/index/landingPage";
	public function getAllowedContextPaths() {
		return array(
			self::CONTEXT_PATH_SEARCH,
			self::CONTEXT_PATH_PRODUCT,
			self::CONTEXT_PATH_CATEGORY,
			self::CONTEXT_PATH_LP
		);
	}

	public function getContextPath() {
		return
			Mage::app()->getRequest()->getModuleName()."/".
			Mage::app()->getRequest()->getControllerName()."/".
			Mage::app()->getRequest()->getActionName();
	}

	public function getVendorDataByUrlKey($urlKey) {
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');

		$query =
			'SELECT `vendor_name`,`vendor_type` FROM `' .
			$resource->getTableName('udropship/vendor') .
			'` WHERE `url_key` = "' . $urlKey . '" LIMIT 1';

		$result = $readConnection->fetchAll($query);

		return count($result) ? current($result) : array();
	}

	public function getShippingMethodName($name) {
		switch($name) {
			case 'udtiership_1':
				$name = 'kurier';
				break;
		}
		return $name;
	}

	public function getPaymentMethodName($name) {
		switch($name) {
			case Zolago_Payment_Model_Gateway::PAYMENT_METHOD_CODE:
				$name = 'dotpay przelew';
				break;

			case Zolago_Payment_Model_Cc::PAYMENT_METHOD_CODE:
				$name = 'dotpay karta';
				break;

			case Mage_Payment_Model_Method_Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE:
				$name = 'przelew';
				break;

			case 'cashondelivery':
				$name = 'pobranie';
				break;
		}

		return $name;
	}

}