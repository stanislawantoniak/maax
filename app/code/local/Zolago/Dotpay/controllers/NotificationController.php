<?php
require_once Mage::getConfig()->getModuleDir('controllers', 'Dotpay_Dotpay') . DS . "NotificationController.php";

class Zolago_Dotpay_NotificationController extends Dotpay_Dotpay_NotificationController
{
	public function indexAction()
	{

		$data = $this->getRequest()->getPost();

		/** @var Mage_Sales_Model_Order $order */
		$order = Mage::getModel('sales/order');
		$order->loadByIncrementId($data['control']);
		if (!$order->getId()) {
			die('ERR');
		}

		if (!$this->isDataIntegrity($order->getPayment()->getMethodInstance()->getConfigData('pin'))) {
			die('ERR');
		}

		list($amount, $currency) = explode(' ', $data['orginal_amount']);
		if (!($order->getOrderCurrencyCode() == $currency && round($order->getGrandTotal(), 2) == $amount)) {
			die('ERR');
		}

		if ($data['t_status'] == 2) {
			$order->addStatusHistoryComment(
				Mage::helper('dotpay')->__('The payment has been accepted.'),
				Mage_Sales_Model_Order::STATE_PROCESSING);
		}

		/* never cancel
				elseif ($this->getRequest()->getPost('t_status') == 3) {
					$order->cancel();
					$order->addStatusToHistory(
						Mage_Sales_Model_Order::STATE_CANCELED,
						Mage::helper('dotpay')->__('The order has been canceled.'));
				}
		*/

		$order->save();
		//Save transaction
		/** @var Zolago_Dotpay_Model_Client $client */
		$client = Mage::getModel("zolagodotpay/client");
		$client->saveTransaction(
			$order,
			$data['amount'],
			$data['t_status'],
			'0', //todo: get provider id
			$data['t_id'],
			'order', //todo: get from const
			$data
		);

		die('OK');
	}

	protected function isDataIntegrity($pin)
	{

		$sellerAccount = new Dotpay_Model_SellerAccount;
		$sellerAccount->
		setId($this->getRequest()->getPost('id'))->
		setPin($pin);

		$customer = new Dotpay_Model_Customer;
		$customer->
		setEmail($this->getRequest()->getPost('email'));

		$transaction = new Dotpay_Model_Transaction;
		$transaction->
		setAmount($this->getRequest()->getPost('amount'))->
		setDescription($this->getRequest()->getPost('description'))->
		setControl($this->getRequest()->getPost('control'))->
		setCode($this->getRequest()->getPost('code'))->
		setSellerAccount($sellerAccount)->
		setCustomer($customer);

		$transactionConfirmation = new Dotpay_Model_TransactionConfirmation;
		$transactionConfirmation->
		setStatus($this->getRequest()->getPost('status'))->
		setTId($this->getRequest()->getPost('t_id'))->
		setOriginalAmount($this->getRequest()->getPost('orginal_amount'))->
		setTStatus($this->getRequest()->getPost('t_status'))->
		setService($this->getRequest()->getPost('service'))->
		setUsername($this->getRequest()->getPost('username'))->
		setPassword($this->getRequest()->getPost('password'))->
		setTransaction($transaction);

		if ($this->getRequest()->getPost('md5') == $transactionConfirmation->computeMd5())
			return TRUE;

		return FALSE;
	}
}