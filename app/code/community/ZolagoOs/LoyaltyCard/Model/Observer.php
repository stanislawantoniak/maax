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
		$subscriber = $subscriber->rawLoadByEmail($email, $storeId);
		
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

	public function attachLoyaltyCardData() {
		$this->attachLoyaltyCardDataToCustomersAccounts();
		$this->attachCustomersToGroupsByLoyaltyCardType();
	}

	/**
	 * Place data from card to customers accounts
	 */
	public function attachLoyaltyCardDataToCustomersAccounts() {
		// get cards collection
		/** @var ZolagoOs_LoyaltyCard_Model_Resource_Card_Collection $cardCollection */
		$cardCollection = Mage::getResourceModel("zosloyaltycard/card_collection");
		$cardCollection->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_ASC);

		// group emails by store_id then email
		$cards = array();
		$emails = array();
		/** @var ZolagoOs_LoyaltyCard_Model_Card $card */
		foreach ($cardCollection as $card) {
			$cards[$card->getStoreId()][$card->getEmail()][] = $card;
			$emails[$card->getStoreId()][] = $card->getEmail();
		}

		// for each store load collection of customers
		foreach ($emails as $storeId => $list) {
			$websiteId = Mage::app()->getStore($storeId)->getWebsiteId();

			/** @var Mage_Customer_Model_Resource_Customer_Collection $customersCollection */
			$customersCollection = Mage::getResourceModel("customer/customer_collection");
			$customersCollection->addFieldToFilter('is_active', 1);
			$customersCollection->addFieldToFilter('website_id', $websiteId);
			$customersCollection->addFieldToFilter('store_id', $storeId);
			$customersCollection->addAttributeToSelect(array(
				'loyalty_card_number_1',
				'loyalty_card_number_2',
				'loyalty_card_number_3',
				'loyalty_card_number_1_type',
				'loyalty_card_number_2_type',
				'loyalty_card_number_3_type',
				'loyalty_card_number_1_expire',
				'loyalty_card_number_2_expire',
				'loyalty_card_number_3_expire',
			));

			// check for each customer that have corresponding cards data
			/** @var Zolago_Customer_Model_Customer $customer */
			foreach ($customersCollection as $customer) {
				$save = false;
				$index = 1;
					/** @var ZolagoOs_LoyaltyCard_Model_Card $_card|null */
				if (isset($cards[$storeId][$customer->getEmail()])) {
					$customerCards = array_replace(array(null, null, null), $cards[$storeId][$customer->getEmail()]);
				} else {
					$customerCards = array(null, null, null);
				}
				foreach ($customerCards as $_card) {
					if ($index > 3) break;
					$n = "loyalty_card_number_{$index}";
					$t = "loyalty_card_number_{$index}_type";
					$e = "loyalty_card_number_{$index}_expire";

					if ($_card) {
						$customer->setData($n, $_card->getCardNumber());
						$customer->setData($t, $_card->getCardType());
						$customer->setData($e, $_card->getExpireDate());
					} else {
						$customer->unsetData($n);
						$customer->unsetData($t);
						$customer->unsetData($e);
					}
					if ($customer->dataHasChangedFor($n)) {
						$customer->getResource()->saveAttribute($customer, $n);
					}
					if ($customer->dataHasChangedFor($t)) {
						$customer->getResource()->saveAttribute($customer, $t);
					}
					if ($customer->dataHasChangedFor($e)) {
						$customer->getResource()->saveAttribute($customer, $e);
					}
					$index++;
				}
			}
		}
	}

	/**
	 * Attach customer to customer groups depends on card type
	 *
	 * @throws Exception
	 * @throws Mage_Core_Exception
	 */
	public function attachCustomersToGroupsByLoyaltyCardType() {
		/** @see app/code/core/Mage/Customer/sql/customer_setup/install-1.6.0.0.php */
		// no const for it?
		$defaultGroupId = 1; // General

		/** @var Mage_Core_Model_Date $dateModel */
		$dateModel = Mage::getModel('core/date');
		$now = $dateModel->timestamp(time());

		/** @var Mage_Core_Model_Website $website */
		foreach (Mage::app()->getWebsites() as $website) {
			/** @var ZolagoOs_LoyaltyCard_Helper_Data $helper */
			$helper = Mage::helper("zosloyaltycard");
			$config = $helper->getLoyaltyCardConfig($website->getDefaultStore());

			/** @var Mage_Customer_Model_Resource_Customer_Collection $coll */
			$coll = Mage::getResourceModel("customer/customer_collection");
			$coll->addAttributeToFilter('website_id', array("eq" => $website->getId()));
			$coll->addAttributeToSelect(array(
				'loyalty_card_number_1',
				'loyalty_card_number_2',
				'loyalty_card_number_3',
				'loyalty_card_number_1_type',
				'loyalty_card_number_2_type',
				'loyalty_card_number_3_type',
				'loyalty_card_number_1_expire',
				'loyalty_card_number_2_expire',
				'loyalty_card_number_3_expire',
			));


			/** @var Zolago_Customer_Model_Customer $customer */
			foreach ($coll as $customer) {
				$groupId = $defaultGroupId;

				$loyaltyCardNumber = $customer->getData('loyalty_card_number_1');
				$loyaltyCardNumber = $customer->getData('loyalty_card_number_2') ? $customer->getData('loyalty_card_number_2') : $loyaltyCardNumber;
				$loyaltyCardNumber = $customer->getData('loyalty_card_number_3') ? $customer->getData('loyalty_card_number_3') : $loyaltyCardNumber;

				$loyaltyCardNumberType = $customer->getData('loyalty_card_number_1_type');
				$loyaltyCardNumberType = $customer->getData('loyalty_card_number_2_type') ? $customer->getData('loyalty_card_number_2_type') : $loyaltyCardNumberType;
				$loyaltyCardNumberType = $customer->getData('loyalty_card_number_3_type') ? $customer->getData('loyalty_card_number_3_type') : $loyaltyCardNumberType;

				$loyaltyCardNumberExpire = $customer->getData('loyalty_card_number_1_expire');
				$loyaltyCardNumberExpire = $customer->getData('loyalty_card_number_2_expire') ? $customer->getData('loyalty_card_number_2_expire') : $loyaltyCardNumberExpire;
				$loyaltyCardNumberExpire = $customer->getData('loyalty_card_number_3_expire') ? $customer->getData('loyalty_card_number_3_expire') : $loyaltyCardNumberExpire;

				$expireTimestamp = $dateModel->timestamp($loyaltyCardNumberExpire);

				if (strlen($loyaltyCardNumber) && $loyaltyCardNumberType && ($now < $expireTimestamp)) {
					if (isset($config[$loyaltyCardNumberType]) &&
						isset($config[$loyaltyCardNumberType]['customer_group_id']) &&
						!empty($config[$loyaltyCardNumberType]['customer_group_id'])
					) {
						/** @var Mage_Customer_Model_Group $group */
						$group = Mage::getModel("customer/group")->load($config[$loyaltyCardNumberType]['customer_group_id']);
						if ($group->getId()) {
							$groupId = $group->getId();
						}
					}
				}

				$customer->setData('group_id', $groupId);
				if ($customer->dataHasChangedFor('group_id')) {
					$customer->save();
				}
			}
		}
	}

	public function deleteSubscriptionAfterDeleteLoyaltyCard(Varien_Event_Observer $observer) {
		/** @var ZolagoOs_LoyaltyCard_Model_Card $card */
		$card = $observer->getDataObject();
		$deleteType = $card->getDeleteType();
		if ($deleteType == ZolagoOs_LoyaltyCard_Model_Card::DELETE_WITH_SUBSCRIPTION) {

			/* @var $subscriber Zolago_Newsletter_Model_Subscriber */
			$subscriber = Mage::getModel('zolagonewsletter/subscriber');
			$email = $card->getEmail();
			$storeId = $card->getStoreId();
			$subscriber = $subscriber->rawLoadByEmail($email, $storeId);

			if ($subscriber->getId()) {
				$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED);
				$subscriber->save();
			} else {
				// oh, rly?
			}
		}
	}

	public function saveLog(Varien_Event_Observer $observer) {
		/** @var ZolagoOs_LoyaltyCard_Model_Card $card */
		$card = $observer->getDataObject();
		/** @var ZolagoOs_LoyaltyCard_Helper_Data $helper */
		$helper = Mage::helper("zosloyaltycard");

		$string = PHP_EOL . 'DATE: ' . Mage::getSingleton('core/date')->gmtDate() . PHP_EOL;

		/* @var $session Zolago_Dropship_Model_Session */
		$session = Mage::getSingleton('udropship/session');
		$vendor = $session->getVendor();
		$operator = $session->getOperator();

		if ($session->isOperatorMode()) {
			$who = $operator->getFullname() . "(" . $operator->getEmail() . ")";
		} else {
			$who = $vendor->getVendorName();
		}

		$isNew = $card->isObjectNew();
		if ($isNew) {
			$string .= "NEW CARD INSERTED BY: {$who}" . PHP_EOL;
		} else {
			if (!$card->isDeleted()) {
				$string .= "CARD UPDATED BY: {$who}" . PHP_EOL;
			} else {
				$string .= "CARD DELETED BY: {$who}" . PHP_EOL;
			}
		}
		$string .= "CARD NUMBER: " . $card->getCardNumber() . PHP_EOL;
		$string .= "CARD EMAIL: " . $card->getEmail() . PHP_EOL;
		$string .= "CARD OWNER: " . $card->getFirstName() . ' ' . $card->getSurname() . PHP_EOL;
		$helper->saveLog($string);
	}
}