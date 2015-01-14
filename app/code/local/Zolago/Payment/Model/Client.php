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
	 * @param int $providerId
	 * @param string $txnId
	 * @param string $txnType
	 * @param array $data
	 * @return bool|int
	 */
	public function saveTransaction($order,$amount,$status,$providerId,$txnId,$txnType,$data=array()) {
		if(is_object($order) && is_numeric($amount) && $providerId && $txnId && $this->validateTransactionStatus($status)) {

			/** @var Mage_Core_Helper_Data $helper */
			$helper = Mage::helper("core");

			$is_closed = $status == self::TRANSACTION_STATUS_NEW ? 0 : 1; // transaction is closed when status is other than new

			$customerId = !$order->getCustomerIsGuest() ? $order->getCustomerId() : 0; //0 for guest

			$transaction = Mage::getResourceModel("sales/payment_transaction");
			$transaction
				->setOrderId($order->getId())
				->setPaymentId($providerId)         //payment provider id
				->setTxnId($txnId)                  //transaction id from payment provider
				->setTxnType($txnType)              //order or refund
				->setIsClosed($is_closed)
				->setAdditionalInformation($helper->jsonEncode($data))
				->setTxnAmount($amount)
				->setTxnStatus($status)
				->setCustomerId($customerId);       //0 is guest;

			$transaction->save();

			if($transaction->getId()) {
				return $transaction->getId();
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
		} else {
			return false;
		}
	}

}