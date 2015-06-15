<?php
	class Zolago_Adminhtml_PaymentController extends Mage_Adminhtml_Controller_Action
	{

		public function massRefundAction()
		{
			$transactions = $this->getRequest()->getParam('txn');

			$skipped = 0;
			$success = 0;
			$error = 0;

			/** @var Zolago_Dotpay_Model_Client $dotpay */
			$dotpay = Mage::getModel("zolagodotpay/client");

			foreach ($transactions as $txnId) {
				/** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
				$transaction = Mage::getModel("sales/order_payment_transaction")->load($txnId);
				if ($transaction->getData('txn_type') == Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND &&
					$transaction->getData('txn_status') == Zolago_Payment_Model_Client::TRANSACTION_STATUS_NEW &&
					$transaction->getData('is_closed') == "0"
				) {

					$order = $transaction->getOrder();
					$payment = $order->getPayment();

					$transaction->setOrderPaymentObject($payment);

					//todo: if we'll add more payment providers handle refunds here

					$paymentMethod = $payment->getMethod();
					$sendEmail = false;
					switch ($paymentMethod) {
						case Zolago_Dotpay_Model_Client::PAYMENT_METHOD: //'dotpay'
							if ($dotpay->makeRefund($order, $transaction)) {
								$sendEmail=true;
								$success++;
							} else {
								$error++;
							}
							break;
					}
					if($sendEmail) {
						//send refund done email
						/** @var Zolago_Payment_Helper_Data $paymentHelper */
						$paymentHelper = Mage::helper('zolagopayment');
						/** @var Zolago_Rma_Helper_Data $rmaHelper */
						$rmaHelper = Mage::helper('zolagorma');

						$email = $order->getCustomerEmail();
						$amount = $paymentHelper->getCurrencyFormattedAmount(abs($transaction->getTxnAmount()));

						$rma = $paymentHelper->getTransactionRma($transaction);
						if($rma) { //refund is for rma
							if($paymentHelper->sendRmaRefundEmail(
								$email,
								$rma,
								$amount,
								$paymentMethod
							)) {
								//if email has been sent then add comments
								$po = $rma->getPo();

								$po->addComment($rmaHelper->__("Email about RMA refund was sent to customer (RMA id: %s, amount: %s)", $rma->getIncrementId(), $amount), false, true);
								$rma->addComment($rmaHelper->__("Email about refund was sent to customer (Amount: %s)", $amount));

								$po->saveComments();
								$rma->saveComments();
							}
						} else {
							$po = $paymentHelper->getTransactionPo($transaction);

							if($paymentHelper->sendRefundEmail(
								$email,
								$order,
								$amount,
								$paymentMethod)
							) {
								//if email has been sent then add comment
								$po->addComment($rmaHelper->__("Email about refund was sent to customer (Amount: %s)", $amount));
								$po->saveComments();
							}
						}
					}
				} else {
					$skipped++;
				}
			}
			if($error) {
				$this->_getSession()->addError($this->__('%s transactions has not been refunded due to an error.', $error));
			}
			if($success) {
				$this->_getSession()->addSuccess($this->__('%s transactions has been successfully refunded.', $success));
			}
			if($skipped) {
				$this->_getSession()->addWarning($this->__('%s transactions has been skipped during refunding due to incorrect status or transaction type.', $skipped));
			}
			$this->_redirectReferer();
		}

		public function refundAction() {

		}
	}