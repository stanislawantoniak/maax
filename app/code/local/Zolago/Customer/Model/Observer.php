<?php
class Zolago_Customer_Model_Observer {
    /**
     * Clear tokens older that limit
     */
    public function cleanOldTokens() {
        $today = new Zend_Date();
        echo (Mage::getResourceModel("zolagocustomer/emailtoken")->cleanOldTokens(
                $today->subHour(Zolago_Customer_Model_Emailtoken::HOURS_EXPIRE))
	        && Mage::getResourceModel("zolagocustomer/attachtoken")->cleanOldTokens(
		    $today->subHour(Zolago_Customer_Model_Attachtoken::HOURS_EXPIRE))
        );
    }

    public function customerChangeEmailConfirm($observer)
    {
//        $event = $observer->getEvent();
//        $customer = $event->getCustomer();
//        $customer->setCustomerConfirmedEmail(true);
//
//        //subscribe
//        /* @var $newsletterInviter Zolago_Newsletter_Model_Subscriber */
//        Mage::getModel('zolagonewsletter/subscriber')
//            ->subscribeCustomer($customer);
    }

	/**
	 * Clear quote presonal data when customer logout
	 * @param type $observer
	 */
	public function customerLogout($observer) {
		/* return because this is already done by app/code/local/Zolago/Persistent/Model/Observer.php:64 */
		return;

		$customer = $observer->getEvent()->getCustomer();
		$checkout = Mage::getModel("checkout/session");
		/* @var $checkout Mage_Checkout_Model_Session */
		$quote = $checkout->getQuote();
		
		/**
		 * Quotes match
		 */
		if($quote->getCustomerId()==$customer->getId()){
			
			// 1. Clear customer data from quote
			$allowedFields = array("customer_id", "customer_is_guest", 
				"customer_group_id", "customer_note", "customer_note_notify");
			
			foreach($quote->getData() as $key=>$value){
				if(preg_match('/^customer_/', $key) && !in_array($key, $allowedFields)){
					$quote->setData($key, null);
				}
			}
			$quote->save();
			
			// 2. Remove addresses
			foreach($quote->getAddressesCollection() as $address){
				$address->delete();
			}
			
			// 3. Remove payments
			foreach($quote->getPaymentsCollection() as $payment){
				$payment->delete();
			}
			
			/**
			 * @todo - add customer is inited to prevent override bys setCustomer method
			 */

		}
	}

	public function attachCustomersToGroupsByLoyaltyCardType() {
		/** @see app/code/core/Mage/Customer/sql/customer_setup/install-1.6.0.0.php */
		// no const for it?
		$defaultGroupId = 1; // General

		/** @var Mage_Core_Model_Date $dateModel */
		$dateModel = Mage::getModel('core/date');
		$now = $dateModel->timestamp(time());

		/** @var Zolago_Customer_Helper_Data $helper */
		$helper = Mage::helper("zolagocustomer");
		$config = $helper->getLoyaltyCardConfig();

	/** @var Mage_Core_Model_Website $website */
		foreach (Mage::app()->getWebsites() as $website) {
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
//				echo $now . " vs " . $expireTimestamp ."\n";

				if (strlen($loyaltyCardNumber) && $loyaltyCardNumberType && ($now < $expireTimestamp)) {
					if (isset($config[$loyaltyCardNumberType]) &&
						isset($config[$loyaltyCardNumberType]['customer_group_id']) &&
						!empty($config[$loyaltyCardNumberType]['customer_group_id'])
					) {
						/** @var Mage_Customer_Model_Group $group */
						$group = Mage::getModel("customer/group")->load($config[$loyaltyCardNumberType]['customer_group_id']);
						if ($group->getId()) {
							$groupId = $group->getId();
							echo "wow set id $groupId \n";
						}
					}
				}


				$customer->setData('group_id', $groupId);
				if ($customer->dataHasChangedFor('group_id')) {
					echo "wow save $groupId!\n";
					$customer->save();
				}
			}
		}
	}


}