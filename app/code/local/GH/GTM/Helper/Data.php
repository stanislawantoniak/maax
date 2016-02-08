<?php

/**
 * Class GH_GTM_Helper_Data
 */
class GH_GTM_Helper_Data extends Shopgo_GTM_Helper_Data {

	public function getVisitorData(){
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


		$data['event'] = 'visitorDataReady';

		return $data;
	}

}