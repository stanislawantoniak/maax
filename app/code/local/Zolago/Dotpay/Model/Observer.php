<?php

class Zolago_Dotpay_Model_Observer {

	public function updateTransactions() {
		Mage::log("WORKS",null,"dotpay_cron.log");
	}
}