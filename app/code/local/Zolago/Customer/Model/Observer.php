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
}