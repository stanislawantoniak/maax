<?php

/**
 * Class Zolago_Payment_Model_Observer
 */
class Zolago_Payment_Model_Observer
{

    public static function processRefunds()
    {
        /* @var $refundsModel Zolago_Payment_Model_Refund */
        $refundsModel = Mage::getModel("zolagopayment/refund");
        $collection = $refundsModel->getTransactionLastOverpayments();

        if (count($collection) > 0) {
            $orderModel = Mage::getModel('sales/order');
            //make refund transactions
	        $rmaId = false;
            foreach ($collection as $item) {
				/** @var Zolago_Payment_Model_Allocation $item */
	            if(isset($item['rma_id']) && !empty($item['rma_id'])) {//indicates that refund that we're going to create contains RMA money
		            $rmaId = $item['rma_id'];
	            }

                $amountToRefund = $item->getMaxAllocationAmount();
                $amount = - $amountToRefund;

                $orderId = $item->getOrderId();
                $parentTransactionId = $item->getTransactionId();

	            /** @var Mage_Sales_Model_Order $order */
                $order = $orderModel->load($orderId);

                $status = Zolago_Payment_Model_Client::TRANSACTION_STATUS_NEW;

	            /** @var Zolago_Payment_Helper_Data $helper */
	            $helper = Mage::helper('zolagopayment');
                $txnId = $helper->RandomStringForRefund();

                $parentsTxtId = $item->getTxnId();
                $txnType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND;

                /* @var $client Zolago_Dotpay_Model_Client */
                $client = Mage::getModel("zolagodotpay/client");
                $refundTransactionId = $client->saveTransaction(
	                $order,
	                $amount,
	                $status,
	                $txnId,
	                $txnType,
                    $client->getDotpayId(),
	                array(),
	                '',
	                $parentTransactionId,
	                $parentsTxtId
                );

	            if($refundTransactionId) {
		            // remove overpay allocation
		            /** @var Zolago_Payment_Model_Allocation $allocation */
		            $allocation = Mage::getModel("zolagopayment/allocation");
		            $allocation->setData(array(
			            'transaction_id' => $item->getData('transaction_id'),
			            'po_id' => $item->getData('po_id'),
			            'allocation_amount' => -$amountToRefund,
			            'allocation_type' => Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_OVERPAY,
			            'operator_id' => null,
			            'created_at' => Mage::getSingleton('core/date')->gmtDate(),
			            'comment' => $helper->__("Moved to refund"),
			            'customer_id' => $item->getData('customer_id'),
			            'vendor_id' => $item->getData('vendor_id'),
			            'is_automat' => 1,
			            'refund_transaction_id' => $refundTransactionId,
			            'rma_id' => ($rmaId ? $rmaId : null)
		            ));
		            $allocation->save();

		            // create refund allocation
		            /** @var Zolago_Payment_Model_Allocation $allocation */
		            $allocation = Mage::getModel("zolagopayment/allocation");
		            $allocation->setData(array(
			            'transaction_id' => $item->getData('transaction_id'),
			            'po_id' => $item->getData('po_id'),
			            'allocation_amount' => $amountToRefund,
			            'allocation_type' => Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_REFUND,
			            'operator_id' => null,
			            'created_at' => Mage::getSingleton('core/date')->gmtDate(),
			            'comment' => $helper->__("Created refund (id: $txnId)"),
			            'customer_id' => $item->getData('customer_id'),
			            'vendor_id' => $item->getData('vendor_id'),
			            'is_automat' => 1,
			            'refund_transaction_id' => $refundTransactionId,
			            'rma_id' => ($rmaId ? $rmaId : null)
		            ));
		            $allocation->save();
	            } else {
		            throw new Mage_Core_Exception("Automatic refund failed for allocation id: ".$item->getId());
	            }
            }
        }
    }

}