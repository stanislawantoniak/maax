<?php
class Zolago_Dotpay_Model_Client extends Zolago_Payment_Model_Client {
	const TRANSACTION_STATUS_PROCESSING_REALIZATION_WAITING = 2;
	const TRANSACTION_STATUS_PROCESSING_REALIZATION = 2;

	public function saveTransaction($order,$amount,$status,$txnId,$txnType,$data=array(),$comment="") {
		if($this->validateData($data)) {
			return parent::saveTransaction($order, $amount, $status, $txnId, $txnType, $data, $comment);
		}
		return false;
	}

	//validate data given by
	protected function validateData($data) {
		return true;
	}
}