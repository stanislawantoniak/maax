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
	 * Save last used payment method and additional data
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
			if($customer->getId()){
				$data = array(
					"method"			=> $payment->getMethod(),
					"additional_information"	=> $payment->getAdditionalInformation()
				);
				$customer->setLastUsedPayment($data);
				$customer->save();
			}
		}
	
	}
}