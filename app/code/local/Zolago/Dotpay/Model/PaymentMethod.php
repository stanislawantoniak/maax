<?php
class Zolago_Dotpay_Model_PaymentMethod extends Dotpay_Dotpay_Model_PaymentMethod {
	/**
	 * Add additional info form payment model to request data
	 * @return array
	 */
	public function getRedirectionFormData() {
		$order = $this->getOrder();
		/* @var $order Mage_Sales_Model_Order */
		$data = array_merge(
			parent::getRedirectionFormData(), 
			// Transfer additional informaiton
			$order->getPayment()->getAdditionalInformation() 
		);
		$data['ch_lock'] = 1;// set lock
		$data['street_n1'] = "_";// street 1
		//$data['street_n2'] = 1;// street 2
		return $data;
	}
}