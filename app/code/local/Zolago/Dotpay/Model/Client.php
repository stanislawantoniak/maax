<?php
class Zolago_Dotpay_Model_Client extends Zolago_Payment_Model_Client {
	//api access data
	private $login;
	private $password;
	private $pin;
	private $dotpay_id;
	private $apiUrl;

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

	//dotpay config paths
	const DOTPAY_PIN_CONFIG_PATH = "payment/dotpay/pin";
	const DOTPAY_ID_CONFIG_PATH = "payment/dotpay/id";
	const DOTPAY_CANCEL_TIME_CONFIG_PATH = "payment/dotpay/cancel_time"; //time in minutes
	const DOTPAY_API_URL_CONFIG_PATH = "payment/dotpay/api_url";
	const DOTPAY_LOGIN_CONFIG_PATH = "payment/dotpay/login";
	const DOTPAY_PASSWORD_CONFIG_PATH = "payment/dotpay/password";

	//dotpay error codes
	const DOTPAY_STATUS_OK = 'OK';
	const DOTPAY_STATUS_ERROR = 'ERR';

	//dotpay refund codes
	const DOTPAY_REFUND_INVALID_AMOUNT = 'INVALID_AMOUNT';

	//payment method database name
	const PAYMENT_METHOD = 'dotpay';

	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param array $data
	 * @return bool|int
	 */
	public function saveTransactionFromPing($order,$data) {
		if($this->validateData($data)) { //first validation
			$status = $this->getOperationStatus($data['operation_status']); //then get status
			$type = $this->getOperationType($data['operation_type']); //and get type
			if($status && $type) { //if they're correct
				return parent::saveTransaction( //trigger parent action
					$order,
					$data['operation_amount'],
					$status,
					$data['operation_number'],
					$type,
					$this->getDotpayId(),
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
		//isset for all because response not always gives all data
		$signature =
			$this->getPin() .
			(isset($data['id']) ? $data['id'] : '') .
			(isset($data['operation_number']) ? $data['operation_number'] : '') .
			(isset($data['operation_type']) ? $data['operation_type'] : '') .
			(isset($data['operation_status']) ? $data['operation_status'] : '') .
			(isset($data['operation_amount']) ? $data['operation_amount'] : '') .
			(isset($data['operation_currency']) ? $data['operation_currency'] : '') .
			(isset($data['operation_original_amount']) ? $data['operation_original_amount'] : '') .
			(isset($data['operation_original_currency']) ? $data['operation_original_currency'] : '') .
			(isset($data['operation_datetime']) ? $data['operation_datetime'] : '') .
			(isset($data['operation_related_number']) ? $data['operation_related_number'] : '') .
			(isset($data['control']) ? $data['control'] : '') .
			(isset($data['description']) ? $data['description'] : '') .
			(isset($data['email']) ? $data['email'] : '') .
			(isset($data['p_info']) ? $data['p_info'] : '') .
			(isset($data['p_email']) ? $data['p_email'] : '') .
			(isset($data['channel']) ? $data['channel'] : '');

		$signature = hash('sha256', $signature);
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

	protected function getExpirationTime() {
		$cancel_time = Mage::getStoreConfig(self::DOTPAY_CANCEL_TIME_CONFIG_PATH);
		$time = strtotime("-$cancel_time minutes");
		return date('Y-m-d H:i:s', Mage::getSingleton('core/date')->timestamp($time));
	}

	public function getDotpayTransactionsToUpdate() {
		$collection = parent::getTransactionsToUpdate(self::PAYMENT_METHOD);
		$collection->addFieldToFilter('dotpay_id',$this->getDotpayId());
		return $collection;
	}

	public function getDotpayTransactionsToCancel() {
		$collection = parent::getTransactionsToCancel(self::PAYMENT_METHOD,$this->getExpirationTime());
		$collection->addFieldToFilter('dotpay_id',$this->getDotpayId());
		return $collection;
	}

	public function getLogin() {
		if(!$this->login) {
			$this->login = Mage::getStoreConfig(self::DOTPAY_LOGIN_CONFIG_PATH);
		}
		return $this->login;
	}

	public function getPassword() {
		if(!$this->password) {
			$this->password = Mage::getStoreConfig(self::DOTPAY_PASSWORD_CONFIG_PATH);
		}
		return $this->password;
	}

	public function getPin() {
		if(!$this->pin) {
			$this->pin = Mage::getStoreConfig(self::DOTPAY_PIN_CONFIG_PATH);
		}
		return $this->pin;
	}

	public function getDotpayId() {
		if(!$this->dotpay_id) {
			$this->dotpay_id = Mage::getStoreConfig(self::DOTPAY_ID_CONFIG_PATH);
		}
		return $this->dotpay_id;
	}

	private function getApiUrl() {
		//return "http://modago.dev/test/curltest.php";
		//Test url: https://ssl.dotpay.pl/test_seller/api/
		//Normal url: https://ssl.dotpay.pl/s2/login/api/
		if(!$this->apiUrl) {
			$this->apiUrl = Mage::getStoreConfig(self::DOTPAY_API_URL_CONFIG_PATH);
		}
		return $this->apiUrl;
	}

	public function dotpayCurl(
			$function=false, //operations or accounts
			$operationNumber=false, //dotpay operation number (example: M1234-5678)
			$method=false, //dotpay api method
			$parameters=array(), //additional parameters as array("key1"=>"value1","key2"=>"value2")
			$usePost=false,
			$postData=false,
			$customRequest="" //custom request that changes method behaviour
	) {
		$login = $this->getLogin();
		$password = $this->getPassword();
		$url = $this->getApiUrl(); //should already contain tracing slash (!!!)

		if($login && $password && $url) {
			$urlData = array();
			if ($function) { // operations or accounts
				$urlData[] = $function;
			}

			if ($operationNumber) {
				$urlData[] = $operationNumber;
			}

			if ($method) {
				$urlData[] = $method;
			}

			$urlData = count($urlData) ? implode("/", $urlData) . "/" : '';

			if ($parameters && count($parameters)) {
				$parameters = "?" . http_build_query($parameters);
				$urlData = $urlData . $parameters;
			}

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url . $urlData);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_CAINFO, Mage::getBaseDir() . "/ca-bundle.crt");
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 100);
			curl_setopt($ch, CURLOPT_USERPWD, $login . ":" . $password);
			if ($method && $customRequest) {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $customRequest);
			}
			if ($usePost) {
				curl_setopt($ch, CURLOPT_POST, 1);
				if($postData) {
					$postData = json_encode($postData);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						'Content-Type: application/json',
						'Content-Length: '.strlen($postData))
					);
				}
			}

			$response = curl_exec($ch);

			curl_close($ch);

			try {
				return Mage::helper('core')->jsonDecode($response);
			} catch(Exception $e) {
				Mage::logException($e);
			}
		}
		return false;
	}

	/**
	 *
	 * @param $txnId
	 * @param bool|array $params
	 * @return bool
	 */
	public function getDotpayTransaction($txnId,$params=false) {
		return $this->dotpayCurl("operations",$txnId,false,$params);
	}

	/**
	 * @param $txnId
	 * @return array|bool
	 */
	public function getDotpayTransactionUpdateFromApi($txnId) {
		$dotpayTransaction = $this->getDotpayTransaction($txnId);
		if(isset($dotpayTransaction['number']) && $dotpayTransaction['number'] == $txnId) {
			return array(
				"txnId" => $dotpayTransaction['number'],
				"orderId" => $dotpayTransaction['control'],
				"txnStatus" => $this->getOperationStatus($dotpayTransaction['status'])
			);
		}
		return false;
	}

	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param Mage_Sales_Model_Order_Payment_Transaction $transaction
	 * @return bool
	 * @throws Exception
	 */
	public function makeRefund($order,$transaction) {
		/** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
		if($transaction->getTxnType() == Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND && //if is refund
			$transaction->getTxnStatus() == Zolago_Payment_Model_Client::TRANSACTION_STATUS_NEW && //and status is new
			$transaction->getTxnAmount() < 0) { //and amount is negative
			$data = array(
				'amount' => abs($transaction->getTxnAmount()),
				'comment' => 'Modago refund id: '.$transaction->getTxnId()
			);

			try {
				$response = $this->dotpayCurl("operations", $transaction->getParentTxnId(), "refund", array(), true, $data);

				if (isset($response['error_code'])) {
					if($response['error_code'] == self::DOTPAY_REFUND_INVALID_AMOUNT) {
						$transaction->setTxnStatus(Zolago_Payment_Model_Client::TRANSACTION_STATUS_REJECTED);
						$transaction->setIsClosed(1);
						$rejected = true;
					}
				} elseif(isset($response['detail']) && $response['detail'] == 'ok') {
					$transaction->setTxnStatus(Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED);
					$transaction->setIsClosed(1);

					//load parent transaction to get dotpay txn_id
					$oldTransactionId = $transaction->getTxnId();
					$newTransactionId = false;
					$parentTxn = $this->getDotpayTransaction(false,array('description'=>$transaction->getParentTxnId()));

					if(isset($parentTxn['count']) && $parentTxn['count'] == 1 && isset($parentTxn['results']) && isset($parentTxn['results'][0])) {
						$newTransactionId = $parentTxn['results'][0]['number'];
						$response = $parentTxn['results'][0];
					} elseif(isset($parentTxn['count']) && $parentTxn['count'] > 1 && isset($parentTxn['results'])) {
						foreach ($parentTxn['results'] as $parentResult) {
							if(isset($parentResult['amount']) && $parentResult['amount'] == abs($transaction->getTxnAmount()) && isset($parentResult['number'])) {
								$otherTransaction = Mage::getModel('sales/order_payment_transaction')->getCollection()->addFieldToFilter('transaction_id',$parentResult['number']);
								if(!$otherTransaction->getSize()) {
									$response = $parentResult;
									$newTransactionId = $parentResult['number'];
									break;
								}
								unset($transactionModel);
							}
						}
					}


					//if dotpay has returned new transaction id then update it in allocations
					if($newTransactionId) {
						$transaction->setTxnId($newTransactionId);
						/** @var Zolago_Payment_Model_Allocation $allocationModel */
						$allocationModel = Mage::getModel('zolagopayment/allocation');
						$allocations = $allocationModel->getCollection()->addFieldToFilter('refund_transaction_id',$transaction->getId());
						foreach($allocations as $allocation) {
							$allocation->setData('comment',str_replace($oldTransactionId,$newTransactionId,$allocation->getComment()));
							$allocation->save();
						}
					}
				}

				$transaction->setAdditionalInformation(
					Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
					$response
				);
				$transaction->save();
				return isset($rejected) && $rejected ? false : true;
			} catch(Exception $e) {
				Mage::logException($e);
			}
		}
		return false;
	}

	/**
	 * @param string $login
	 * @return Zolago_Dotpay_Model_Client $this
	 */
	public function setLogin($login) {
		$this->login = $login;
		return $this;
	}

	/**
	 * @param string $password
	 * @return Zolago_Dotpay_Model_Client $this
	 */
	public function setPassword($password) {
		$this->password = $password;
		return $this;
	}

	/**
	 * @param string $pin
	 * @return Zolago_Dotpay_Model_Client $this
	 */
	public function setPin($pin) {
		$this->pin = $pin;
		return $this;
	}

	/**
	 * @param string|int $dotpay_id
	 * @return Zolago_Dotpay_Model_Client $this
	 */
	public function setDotpayId($dotpay_id) {
		$this->dotpay_id = $dotpay_id;
		return $this;
	}
}