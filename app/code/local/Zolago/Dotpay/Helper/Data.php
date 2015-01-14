<?php

class Zolago_Dotpay_Helper_Data extends Dotpay_Dotpay_Helper_Data {

	public function getTransactionsToUpdate() {
		$collection = Mage::getResourceModel("zolagosales/transactions_collection");
	}

}