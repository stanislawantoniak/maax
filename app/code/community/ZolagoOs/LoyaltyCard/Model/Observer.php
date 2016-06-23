<?php

/**
 * Class ZolagoOs_LoyaltyCard_Model_Observer
 */
class ZolagoOs_LoyaltyCard_Model_Observer {

	/**
	 * After card save:
	 * insert new subscriber with confirmed email
	 *
	 * @param Varien_Event_Observer $observer
	 * @event loyalty_card_save_after
	 */
	public function attachNewSubscriberFromLoyaltyCard(Varien_Event_Observer $observer) {
		/** @var ZolagoOs_LoyaltyCard_Model_Card $card */
		$card = $observer->getDataObject();

		/* @var $subscriber Zolago_Newsletter_Model_Subscriber */
		$subscriber = Mage::getModel('zolagonewsletter/subscriber');
		$email = $card->getEmail();
		$storeId = $card->getStoreId();
		$subscriber->rawLoadByEmail($email, $storeId);
		
		if ($subscriber->getId()) {
			// if exist just confirm email
			$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
		} else {
			// if not just copy data from card to new model

			/** @var Zolago_Customer_Model_Customer $customer */
			$customer = Mage::getModel('zolagocustomer/customer');
			$customer->setWebsiteId(Mage::app()->getStore($storeId)->getWebsiteId());
			/** @var Mage_Customer_Model_Resource_Customer $customerRes */
			$customerRes = Mage::getModel('zolagocustomer/customer')->getResource();
			$customerRes->loadByEmail($customer, $email);

			$customerId = $customer->getId() ? $customer->getId() : 0;

			$subscriber->setStoreId($storeId);
			$subscriber->setCustomerId($customerId);
			$subscriber->setEmail($email);
			$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
			$subscriber->setSubscriberFirstname($card->getFirstName());
			$subscriber->setSubscriberLastname($card->getSurname());
		}
		$subscriber->save();
	}
}