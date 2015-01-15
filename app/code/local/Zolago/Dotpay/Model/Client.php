<?php
class Zolago_Dotpay_Model_Client extends Zolago_Payment_Model_Client {
	//operation statuses
	const DOTPAY_OPERATION_STATUS_NEW                               = 'new';                               //nowa
	const DOTPAY_OPERATION_STATUS_PROCESSING                        = 'processing';                        //przetwarzana
	const DOTPAY_OPERATION_STATUS_COMPLETED                         = 'completed';                         //wykonana
	const DOTPAY_OPERATION_STATUS_REJECTED                          = 'rejected';                          //odrzucona
	const DOTPAY_OPERATION_STATUS_PROCESSING_REALIZATION_WAITING    = 'processing_realization_waiting';    //oczekuje na realizację
	const DOTPAY_OPERATION_STATUS_PROCESSING_REALIZATION            = 'processing_realization';            //realizowana

	//operation types
	const DOTPAY_OPERATION_TYPE_PAYMENT                             = 'payment';                           //płatność
	const DOTPAY_OPERATION_TYPE_PAYMENT_MULTIMERCHANT_CHILD         = 'payment_multimerchant_child';       //płatność multimerchant
	const DOTPAY_OPERATION_TYPE_PAYMENT_MULTIMERCHANT_PARENT        = 'payment_multimerchant_parent';      //nadpłatność multimerchant
	const DOTPAY_OPERATION_TYPE_REFUND                              = 'refund';                            //zwrot
	const DOTPAY_OPERATION_TYPE_PAYOUT                              = 'payout';                            //wypłata
	const DOTPAY_OPERATION_TYPE_RELEASE_ROLLBACK                    = 'release_rollback';                  //zwolnienie rollbacka
	const DOTPAY_OPERATION_TYPE_UNIDENTIFIED_PAYMENT                = 'unidentified_payment';              //płatność niezidentyfikowana
	const DOTPAY_OPERATION_TYPE_COMPLAINT                           = 'complaint';                         //reklamacja

	//dotpay pin config path
	const DOTPAY_PIN_CONFIG_PATH = "payment/dotpay/pin";

	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param array $data
	 * @return bool|int
	 */
	public function saveTransactionFromPing($order,$data) {
		if($this->validateData($data)) { //first validation
			$status = $this->getOperationStatus($data['operation_status']); //then get status
			$type = $this->getOperationType($data); //and get type
			if($data['operation_status'] && $data['operation_type']) { //if they're correct
				return parent::saveTransaction( //trigger parent action
					$order,
					$data['operation_amount'],
					$status,
					$data['operation_number'],
					$type,
					$data);
			}
		}
		return false; //if not return false
	}

	/**
	 * validate data provided by dotpay post to urlc
	 * @param array $data
	 * @return bool
	 */
	public function validateData($data) {
		$PIN = Mage::getStoreConfig(self::DOTPAY_PIN_CONFIG_PATH);
		Mage::log($PIN);
		$signature=
			$PIN.
			$data['id'].
			$data['operation_number'].
			$data['operation_type'].
			$data['operation_status'].
			$data['operation_amount'].
			$data['operation_currency'].
			$data['operation_original_amount'].
			$data['operation_original_currency'].
			$data['operation_datetime'].
			$data['operation_related_number'].
			$data['control'].
			$data['description'].
			$data['email'].
			$data['p_info'].
			$data['p_email'].
			$data['channel'];

		$signature=hash('sha256', $signature);
		Mage::log($signature,null,"dotpay_sign.log");
		Mage::log($data['signature'],null,"dotpay_sign.log");
		return $signature == $data['signature'];
	}

	/**
	 * map dotpay status to transaction status
	 * @param string $status
	 * @return bool|int
	 */
	protected function getOperationStatus($status) {
		switch($status) {
			case self::DOTPAY_OPERATION_STATUS_NEW:
				return self::TRANSACTION_STATUS_NEW;

			case self::DOTPAY_OPERATION_STATUS_PROCESSING:
			case self::DOTPAY_OPERATION_STATUS_PROCESSING_REALIZATION:
			case self::DOTPAY_OPERATION_STATUS_PROCESSING_REALIZATION_WAITING:
				return self::TRANSACTION_STATUS_PROCESSING;

			case self::DOTPAY_OPERATION_STATUS_COMPLETED:
				return self::TRANSACTION_STATUS_COMPLETED;

			case self::DOTPAY_OPERATION_STATUS_REJECTED:
				return self::TRANSACTION_STATUS_REJECTED;
		}
		return false;
	}

	/**
	 * map dotpay transaction type to magento transaction type
	 * @param string $type
	 * @return bool|string
	 */
	protected function getOperationType($type) {
		//todo: check if logic is correct here
		switch($type) {
			case self::DOTPAY_OPERATION_TYPE_PAYMENT:
			case self::DOTPAY_OPERATION_TYPE_UNIDENTIFIED_PAYMENT:
				return Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER;

			case self::DOTPAY_OPERATION_TYPE_REFUND:
				return Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND;

			case self::DOTPAY_OPERATION_TYPE_PAYOUT:
			case self::DOTPAY_OPERATION_TYPE_RELEASE_ROLLBACK:
			case self::DOTPAY_OPERATION_TYPE_PAYMENT_MULTIMERCHANT_CHILD:
			case self::DOTPAY_OPERATION_TYPE_PAYMENT_MULTIMERCHANT_PARENT:
			case self::DOTPAY_OPERATION_TYPE_COMPLAINT:
				return false;
		}
		return false;
	}
}