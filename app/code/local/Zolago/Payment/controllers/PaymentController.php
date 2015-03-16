<?php
	class Zolago_Payment_PaymentController extends Mage_Adminhtml_Controller_Action {

		public function refundAction() {
			var_dump($this->getRequest()->getData());
			return;

			/** @var Zolago_Dotpay_Model_Client $dotpay */
			/*$dotpay = Mage::getModel("zolagodotpay/client");

			$request = $this->getRequest();
			$orderId = $request->getParam('orderid');*/
			/** @var Zolago_Sales_Model_Order $order */
			/*$order = Mage::getModel("sales/order")->load($orderId);

			$txnId = $request->getParam('txnid');

			$dotpay->makeRefund($order,$txnId);*/
		}
	}