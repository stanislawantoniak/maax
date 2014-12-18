<?php
class Zolago_Customer_Model_Observer {
    /**
     * Clear tokens older that limit
     */
    public function cleanOldTokens() {
        $today = new Zend_Date();
        echo Mage::getResourceModel("zolagocustomer/emailtoken")->cleanOldTokens(
                $today->subHour(Zolago_Customer_Model_Emailtoken::HOURS_EXPIRE)
        );
    }
	
	/**
	 * Clear quote presonal data when customer logout
	 * @param type $observer
	 */
	public function customerLogout($observer) {
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
	
	/**
	 * Save last used payment method and additional data
	 * @todo handle last choosed via interfaace method, not really saved - its mapped
	 * @param type $observer
	 */
	public function salesOrderPaymentSaveAfter($observer) {
		$payment = $observer->getEvent()->getDataObject();
		/* @var $payment Mage_Sales_Model_Order_Payment */
		if($payment->getId() && $payment->getOrder()){
			$order = $payment->getOrder();
			/* @var $order Mage_Sales_Model_Order */
			$customer = Mage::getModel("customer/customer");
			if($order->getCustomerId()){
				$customer->load($order->getCustomerId());
			}
			if($customer->getId() && Mage::getSingleton('customer/session')->getTransferPayment(true)){
				$data = array(
					"method"			=> $payment->getMethod(),
					"additional_information"	=> $payment->getAdditionalInformation()
				);
				$customer->setLastUsedPayment($data)->save();
			}
		}
	
	}
}