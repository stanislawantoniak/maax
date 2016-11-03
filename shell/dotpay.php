<?php

require_once 'abstract.php';

class Dotpay extends Mage_Shell_Abstract {

	/**
	 * Run script
	 *
	 * @return void
	 */
	public function run() {
		$action = $this->getArg('action');
		if (empty($action)) {
			echo $this->usageHelp();
		} else {
			$actionMethodName = $action . 'Action';
			if (method_exists($this, $actionMethodName)) {
				$this->$actionMethodName();
			} else {
				echo "Action $action not found!\n";
				echo $this->usageHelp();
				exit(1);
			}
		}
	}


	/**
	 * Retrieve Usage Help Message
	 *
	 * @return string
	 */
	public function usageHelp() {
		$help = 'Available actions: ' . "\n";
		$methods = get_class_methods($this);
		foreach ($methods as $method) {
			if (substr($method, -6) == 'Action') {
				$help .= '    -action ' . substr($method, 0, -6);
				$helpMethod = $method . 'Help';
				if (method_exists($this, $helpMethod)) {
					$help .= "\n".$this->$helpMethod();
				}
				$help .= "\n";
			}
		}
		return $help;
	}

	/**
	 * Funkcja do testowego opłacania zamówienia dotpay
	 *
	 * php shell/dotpay -action makePay -poid Y -amount 10.00
	 *
	 */
	public function makePayAction() {

		$testFlag = (string)Mage::getConfig()->getNode('global/test_server');
		if ($testFlag == 'true') {
			$poid = $this->getArg('poid');
			$amount = (float)$this->getArg('amount');
			/** @var Zolago_Po_Model_Po $po */
			$po = Mage::getModel("zolagopo/po")->load($poid);
			/** @var Zolago_Dotpay_Model_Client $client */
			$client = Mage::getModel("zolagodotpay/client", $po->getStore());
			$client->saveTransaction(
				$po->getOrder(),
				$amount,
				Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED,
				"TEST_" . date("c"),
				Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER,
				Mage::app()->getStore($po->getStore())->getConfig("payment/dotpay/id")
			);
		} else {
			die("Only on test server");
		}
	}

	public function makePayActionHelp() {
		return "use ex: php shell/dotpay -action makePay -poid Y -amount 10.00";
	}

}

$shell = new Dotpay();
$shell->run();