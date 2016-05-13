<?php

/**
 * payment helper
 */
class  Zolago_Payment_Helper_Data extends Mage_Core_Helper_Abstract
{

	/**
	 * @param Zolago_Po_Model_Po|int $poId
	 * @return Zolago_Payment_Model_Resource_Allocation_Collection
	 */
    public function getOverpaymentDetails($poId) {

        return $this->_addOverpaymentJoins($this->_getModel()->getPoOverpayments($poId));
    }


	/**
	 * @param Zolago_Po_Model_Po|int $poId
	 * @return Zolago_Payment_Model_Resource_Allocation_Collection
	 */
    public function getPaymentDetails($poId)  {
	    
        return $this->_addPaymentsJoins($this->_getModel()->getPoPayments($poId));
    }

	/**
	 * @return Zolago_Payment_Model_Allocation
	 */
	private function _getModel() {
		return Mage::getModel('zolagopayment/allocation');
	}

	/**
	 * @param Zolago_Payment_Model_Resource_Allocation_Collection $collection
	 * @return Zolago_Payment_Model_Resource_Allocation_Collection
	 */
	private function _addPaymentsJoins(Zolago_Payment_Model_Resource_Allocation_Collection $collection) {
		$collection
			->joinTransactions()
			->joinOperators()
			->joinPos()
            ->joinVendors();

		return $collection;
	}

	private function _addOverpaymentJoins(Zolago_Payment_Model_Resource_Allocation_Collection $collection) {
		$collection
			->joinTransactions();

		return $collection;
	}

    public function RandomStringForRefund()
    {
        return MD5(strrev(microtime()));
    }

	/**
	 * @param string $email
	 * @param Zolago_Rma_Model_Rma $rma
	 * @param float $refundAmount
	 * @param bool|string $paymentType
	 * @return bool
	 */
	public function sendRmaRefundEmail($email,$rma,$refundAmount,$paymentType=false) {
		/** @var Zolago_Common_Helper_Data $helper */
		$helper = Mage::helper("zolagocommon");

		Mage::helper('udropship')->setDesignStore($rma->getPo()->getOrder()->getStore());

		$return =  $helper->sendEmailTemplate(
			$email,
			'',
			$this->_getRmaRefundEmailTemplateId(),
			$this->_getRmaRefundEmailVars($rma->getIncrementId(),$refundAmount,$paymentType),
			true,
			$this->_getRefundEmailSender()
		);

		Mage::helper('udropship')->setDesignStore();

		return $return;
	}


	/**
	 * @param string $email
	 * @param Mage_Sales_Model_Order $order
	 * @param float $refundAmount
	 * @param bool|string $paymentType
	 * @return Mage_Core_Model_Email_Template_Mailer
	 */
	public function sendRefundEmail($email,$order,$refundAmount,$paymentType=false) {
		/** @var Zolago_Common_Helper_Data $helper */
		$helper = Mage::helper("zolagocommon");

		Mage::helper('udropship')->setDesignStore($order->getStore());

		$return = $helper->sendEmailTemplate(
			$email,
			'',
			$this->_getRefundEmailTemplateId(),
			$this->_getRefundEmailVars($order->getIncrementId(),$refundAmount,$paymentType),
			true,
			$this->_getRefundEmailSender()
		);

		Mage::helper('udropship')->setDesignStore();

		return $return;
	}

	protected function _getRefundEmailTemplateId() {
		return Mage::getStoreConfig("payment_refunds/payment_refunds_emails/refund_email_template");
	}

	protected function _getRmaRefundEmailTemplateId() {
		return Mage::getStoreConfig("payment_refunds/payment_refunds_emails/refund_rma_email_template");
	}

	protected function _getRmaRefundEmailVars($rmaId,$refundAmount,$paymentType) {
		return array(
			'store_name' => Mage::app()->getStore()->getName(),
			'rma_id' => $rmaId,
			'return_amount' => $refundAmount,
			'payment_type' => $paymentType,
			'use_attachments' => true
		);
	}

	protected function _getRefundEmailVars($orderId,$refundAmount,$paymentType) {
		return array(
			'store_name' => Mage::app()->getStore()->getName(),
			'order_id' => $orderId,
			'return_amount' => $refundAmount,
			'payment_type' => $paymentType,
			'use_attachments' => true
		);
	}

	protected function _getRefundEmailSender() {
		return Mage::getStoreConfig("payment_refunds/payment_refunds_emails/refund_email_identity");
	}

	/**
	 * @param Mage_Sales_Model_Order_Payment_Transaction $transaction
	 * @return bool|Zolago_Rma_Model_Rma
	 */
	public function getTransactionRma($transaction) {
		/** @var Zolago_Payment_Model_Allocation $allocationModel */
		$allocationModel = Mage::getModel('zolagopayment/allocation');
		$collection =
			$allocationModel
				->getCollection()
				->addFieldToFilter('refund_transaction_id',$transaction->getId())
				->addFieldToFilter('allocation_type',Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_REFUND);

		if($collection->getSize()) {
			foreach($collection as $allocation) {
				if($allocation->getRmaId()) {
					/** @var Zolago_Rma_Model_Rma $rma */
					$rma = Mage::getModel('zolagorma/rma');
					$rma->load($allocation->getRmaId());
					if($rma->getId()) {
						return $rma;
					}
				}
			}
		}
		return false;
	}

	/**
	 * @param Mage_Sales_Model_Order_Payment_Transaction $transaction
	 * @return bool|Zolago_Po_Model_Po
	 */
	public function getTransactionPo($transaction) {
		/** @var Zolago_Payment_Model_Allocation $allocationModel */
		$allocationModel = Mage::getModel('zolagopayment/allocation');
		$collection =
			$allocationModel
				->getCollection()
				->addFieldToFilter('refund_transaction_id',$transaction->getId())
				->addFieldToFilter('allocation_type',Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_REFUND);
		if($collection->getSize()) {
			$poRefundSum = 0;
			$poId = false;
			foreach($collection as $allocation) {
				$poRefundSum += abs($allocation->getAllocationAmount());
				if(!$poId) {
					$poId = $allocation->getPoId();
				}
			}
			if($poRefundSum == abs($transaction->getTxnAmount())) {
				/** @var Zolago_Po_Model_Po $rma */
				$po = Mage::getModel('zolagopo/po');
				$po->load($poId);
				if($po->getId()) {
					return $po;
				}
			}
		}
		return false;
	}

	public function getCurrencyFormattedAmount($amount) {
		return Mage::helper('core')->currency(
			$amount,
			true,
			false
		);
	}

	/**
	 * @param null $store
	 * @return bool
	 */
	public function getConfigUseAllocation($store = null) {
		$config = Mage::getStoreConfig('payment/config/use_allocation', $store);
		return (bool)$config;
	}
}