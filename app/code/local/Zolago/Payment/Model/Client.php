<?php
abstract class Zolago_Payment_Model_Client {
	const TRANSACTION_STATUS_NEW = 1;
	const TRANSACTION_STATUS_COMPLETED = 2;
	const TRANSACTION_STATUS_REJECTED = 3;


	protected function _connect() {
		return;
	}

	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param float $amount
	 * @param int $status
	 * @param string $txnId
	 * @param string $txnType
	 * @param array $data
	 * @param string $comment
	 * @return bool|int
	 * @throws Exception
	 */
	public function saveTransaction($order,$amount,$status,$txnId,$txnType,$data=array(),$comment="") {
		if($order instanceof Mage_Sales_Model_Order
			&& is_numeric($amount)
			&& $txnId
			&& $this->validateTransactionStatus($status)
			&& $this->validateTransactionType($txnType)
		) {

			$is_closed = $status == self::TRANSACTION_STATUS_NEW ? 0 : 1; // transaction is closed when status is other than new

			/** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
			$transaction = Mage::getModel("sales/order_payment_transaction");
			$transaction->setOrderPaymentObject($order->getPayment());
			$transaction->loadByTxnId($txnId);

			if(!$transaction->getId()) {
				//create new transaction
				$customerId = !$order->getCustomerIsGuest() ? $order->getCustomerId() : 0; //0 for guest

				$transaction
					->setTxnId($txnId)
					->setTxnType($txnType)
					->setIsClosed($is_closed)
					->setTxnAmount($amount)
					->setTxnStatus($status)
					->setCustomerId($customerId);

			} elseif($transaction->getId() && !$transaction->getIsClosed() ) {
				//update existing transaction
				$transaction
					->setIsClosed($is_closed)
					->setTxnStatus($status);
			} else {
				$transaction = false; //because transaction with this txn_id is already closed
			}

			if($transaction instanceof Mage_Sales_Model_Order_Payment_Transaction) {

				$transaction->setAdditionalInformation(
					Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
					$data
				);

				$transaction->save();

				if ($transaction->getId()) {
					if($is_closed) {
						Mage::dispatchEvent(
							"zolagopayment_append_allocation",
							array(
								"transaction_id" => $transaction->getId(),
								"allocation_type" => Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT,
								"operator_id" => null,
								"comment" => $comment
							)
						);
					}

					return $transaction->getId();
				}
			}
		}
		return false;
	}

	public function getTransactionsToUpdate($providerId) {
		$transactions = Mage::getResourceModel("zolagosales/payment_transaction")->getCollection()
			->addFieldToFilter('is_closed',0)
			->addFieldToFilter('payment_id',$providerId)
			->load();
		return $transactions;
	}

	protected function validateTransactionStatus($status) {
		if($status == self::TRANSACTION_STATUS_NEW ||
			$status == self::TRANSACTION_STATUS_COMPLETED ||
			$status == self::TRANSACTION_STATUS_REJECTED) {
			return true;
		}
		return false;
	}

	protected function validateTransactionType($type) {
		if($type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH ||
			$type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE ||
			$type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER ||
			$type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_PAYMENT ||
			$type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND ||
			$type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID) {
			return true;
		}
		return false;
	}

}