<?php
class Zolago_Payment_Model_Client {
	const TRANSACTION_STATUS_NEW = 1;
	const TRANSACTION_STATUS_PROCESSING = 2;
	const TRANSACTION_STATUS_COMPLETED = 3;
	const TRANSACTION_STATUS_REJECTED = 4;
	const PAYMENT_METHOD = false;

	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param float $amount
	 * @param int $status
	 * @param string $txnId
	 * @param string $txnType
	 * @param array $data
	 * @param string $comment
	 * @param null|int $parentTrId
	 * @param null|string $parentTxnId
	 * @return bool|int
	 * @throws Exception
	 */
    public function saveTransaction($order, $amount, $status, $txnId, $txnType, $dotpayId='', $data = array(), $comment = "", $parentTrId = null, $parentTxnId = null)
    {
        if ($order instanceof Mage_Sales_Model_Order
            && is_numeric($amount)
            && $txnId
            && $this->validateTransactionStatus($status)
            && $this->validateTransactionType($txnType)
        ) {

            /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
            $transaction = Mage::getModel("sales/order_payment_transaction");
            $transaction->setOrderPaymentObject($order->getPayment());
            $transaction->loadByTxnId($txnId);

            if (!$transaction->getId()) {
                //create new transaction
                $customerId = !$order->getCustomerIsGuest() ? $order->getCustomerId() : null; //null for guest

                $transaction
                    ->setTxnId($txnId)
                    ->setTxnType($txnType)
                    ->setIsClosed($this->getIsClosedByStatus($status))
                    ->setTxnAmount($amount)
                    ->setTxnStatus($status)
                    ->setParentId($parentTrId)
                    ->setParentTxnId($parentTxnId)
                    ->setCustomerId($customerId)
	                ->setDotpayId($dotpayId);

            } elseif ($transaction->getId() && !$transaction->getIsClosed()) {
                //update existing transaction
                $transaction
                    ->setIsClosed($this->getIsClosedByStatus($status))
                    ->setTxnStatus($status);
            } else {
                return $transaction->getId(); //because transaction with this txn_id is already closed
            }

            if ($transaction instanceof Mage_Sales_Model_Order_Payment_Transaction) {

                if (count($data)) {
                    $transaction->setAdditionalInformation(
                        Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
                        $data
                    );
                }

                $transaction->save();

                //there is no sales_order_payment_transaction_after/before_save
                if ($transaction->getId() && $status == self::TRANSACTION_STATUS_COMPLETED) {
                    Mage::dispatchEvent(
                        "zolagopayment_save_transaction_after",
                        array(
                            "transaction" => $transaction,
                            "allocation_type" => Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT,
                            "operator_id" => null,
                            "comment" => $comment
                        )
                    );
                }
            }
        }
	    return isset($transaction) && $transaction instanceof Mage_Sales_Model_Order_Payment_Transaction ? $transaction->getId() : false;
    }

	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param string $txnId
	 * @param int $status
	 * @return bool|Mage_Sales_Model_Order_Payment_Transaction
	 */
	public function updateTransaction($order,$txnId,$status) {
		if($order instanceof Mage_Sales_Model_Order && $this->validateTransactionStatus($status)) {
			/** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
			$transaction = Mage::getModel("sales/order_payment_transaction");
			$transaction
				->setOrderPaymentObject($order->getPayment())
				->loadByTxnId($txnId);

			$transaction
				->setTxnStatus($status)
				->setIsClosed($this->getIsClosedByStatus($status));

			return $transaction->save();
		}
		return false;
	}

	public function getTransactionsToUpdate($method) {
		$transactions = $this->getTransactions($method);
		if($transactions instanceof Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection) {
			$transactions
				->addFieldToFilter('is_closed', 0);
			return $transactions;
		}
		return false;
	}

	public function getTransactionsToCancel($method,$expiration) {
		$transactions = $this->getTransactions($method);
		if($transactions instanceof Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection
			&& $expiration) {
			$transactions
				->addFieldToFilter('txn_status',self::TRANSACTION_STATUS_NEW)
				->addFieldToFilter('main_table.created_at', array('lt' => $expiration));
			return $transactions;
		}
		return false;
	}

	protected function getTransactions($method) {
		if($method) {
			/** @var Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection $transactions */
			$transactions = Mage::getResourceModel('sales/order_payment_transaction_collection');
			$transactions
				->getSelect()
				->joinLeft(array('payment_id_table'=>'sales_flat_order_payment'),
				"`main_table`.`payment_id` = `payment_id_table`.`entity_id`",
				"payment_id_table.method AS payment_method")
				->where("`payment_id_table`.`method` = ?",$method);

			return $transactions;
		}
		return false;
	}

	protected function validateTransactionStatus($status) {
		if    ($status == self::TRANSACTION_STATUS_NEW
			|| $status == self::TRANSACTION_STATUS_PROCESSING
			|| $status == self::TRANSACTION_STATUS_COMPLETED
			|| $status == self::TRANSACTION_STATUS_REJECTED) {
			return true;
		}
		return false;
	}

	protected function validateTransactionType($type) {
		if ($type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH
			|| $type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE
			|| $type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER
			|| $type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_PAYMENT
			|| $type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND
			|| $type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID) {
			return true;
		}
		return false;
	}

	protected function getIsClosedByStatus($status) {
		if($status == self::TRANSACTION_STATUS_COMPLETED
			|| $status == self::TRANSACTION_STATUS_REJECTED) {
			return 1;
		}
		return 0;
	}

}