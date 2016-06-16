<?php

class Zolago_Payment_Model_Resource_Allocation extends Mage_Core_Model_Resource_Db_Abstract {

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('zolagopayment/allocation', "allocation_id");
    }

	/**
	 * @param $poId
	 * @return float
	 */
    public function getSumOfAllocations($poId) {
        $tableAllo = $this->getTable('zolagopayment/allocation');
        $select = $this->getReadConnection()->select();

        $select->from(array("a" => $tableAllo), "SUM(a.allocation_amount) as sum");
        $select->where("a.po_id = ?" , $poId);
        $select->where("a.allocation_type = ?", Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT);

        $sum = $this->getReadConnection()->fetchRow($select);
        return $sum['sum'];
    }

    public function getDataAllocationForTransaction($transaction, $allocation_type, $operator_id = null, $comment = '') {
        /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
        $tableSPT = $this->getTable('sales/payment_transaction');
        $select = $this->getReadConnection()->select();

        $select->from(array("pt" => $tableSPT), 'pt.order_id');
        $select->where('pt.transaction_id = ?', $transaction->getId());
        $select->where('pt.txn_status = ?',Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED);

        $ordersIDs = $this->getReadConnection()->fetchAll($select);

        $tablePo = $this->getTable("udpo/po");
        $select = $this->getReadConnection()->select();

        $select->from(array("po" => $tablePo));
        $select->where("po.order_id IN(?)", $ordersIDs);
        $select->joinLeft(array("order" => $this->getTable("sales/order")),
            "order.entity_id = po.order_id",
            "order.customer_is_guest");

        $poData = $this->getReadConnection()->fetchAll($select);

        $txnAmount = $transaction->getTxnAmount();
        $flagBreak = false;
        foreach ($poData as $po) {
            $out = array(
                'transaction_id'    => $transaction->getId(),
                'po_id'             => $po['entity_id'],
                'allocation_type'   => $allocation_type,
                'operator_id'       => $operator_id,
                'created_at'        => Mage::getSingleton('core/date')->gmtDate(),
                'comment'           => $comment,
                'customer_id'       => ((!$po['customer_is_guest']) && !is_null($po['customer_is_guest'])) ? $po['customer_id'] : null,
                'vendor_id'         => Mage::getModel("zolagopo/po")->load($po['entity_id'])->getVendor()->getId(),
                'is_automat'        => $this->getAllocationModel()->isAutomat(),
                'primary'           => 1 //indicates that allocation was made directly from transaction - it's the first one
            );

            if ($po === end($poData)) {
                //is last
                $out['allocation_amount'] = $txnAmount;
            } elseif ($txnAmount >= $po['grand_total_incl_tax']) {
                $txnAmount -= $po['grand_total_incl_tax'];
                $out['allocation_amount'] = $po['grand_total_incl_tax'];
            } else {
                $out['allocation_amount'] = $txnAmount;
                $flagBreak = true;
            }

            $data[] = $out;
            if ($flagBreak) {
                break;
            }
        }
        return $data;
    }

	/**
	 * patam data as:
	 * array(
	 *    'transaction_id'    => $transaction_id,
	 *    'po_id'             => $po_id,
	 *    'allocation_amount' => $allocation_amount,
	 *    'allocation_type'   => $allocation_type,
	 *    'operator_id'       => $operator_id,
	 *    'created_at'        => Mage::getSingleton('core/date')->gmtDate(),
	 *    'comment'           => $comment
	 *    'customer_id'       => $po['customer_id']));
	 *    'vendor_id'         => $po['vendor_id']
	 *    'is_automat'        => 0/1
	 *    'refund_transaction_id' => $refundTransactionId
	 *    'rma_id'            => $rmaId
	 *
	 * @param $data
	 */
	public function appendAllocations($data) {
		$writeConnection = $this->_getWriteAdapter();
		$writeConnection->insertMultiple($this->getTable('zolagopayment/allocation'), $data);
	}

    /**
     * @return false|Mage_Core_Model_Abstract|Zolago_Payment_Model_Allocation
     */
    protected function getAllocationModel() {
        return Mage::getModel('zolagopayment/allocation');
    }

}

