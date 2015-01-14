<?php
abstract class Zolago_Payment_Model_Client {

	public function _connect($url) {

	}

	public function saveTransaction() {

	}

	public function getTransactionsToUpdate() {
		$transactions = Mage::getResourceModel("zolagopayment/transactions")->getCollection()
			->addFieldToFilter('status',1);
		//above should return transactions with all statuses that are not: complete or cancelled or rejected
	}

}