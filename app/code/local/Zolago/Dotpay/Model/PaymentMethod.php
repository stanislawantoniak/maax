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
			// Offset needed data
			$this->getOffsetData($order),
			// Transfer additional informaiton
			$order->getPayment()->getAdditionalInformation(),
			// Original data
			parent::getRedirectionFormData()
		);
		
		Mage::log($data);
		return $data;
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @return array
	 */
	public function getOffsetData(Mage_Sales_Model_Order $order) {
		$store = $order->getStore();
		return array(
			"ch_lock" => 1,
			"street_n1" => "_",
			//"street_n2" => "_",
			"p_info"=> $store->getConfig('payment/zolagopayment_gateway/p_info'),
			"p_email"=> $store->getConfig('payment/zolagopayment_gateway/p_email')
		);
	}
}