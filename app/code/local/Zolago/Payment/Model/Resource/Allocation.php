<?php

class Zolago_Payment_Model_Resource_Allocation extends Mage_Core_Model_Resource_Db_Abstract {

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('zolagopayment/allocation', "allocation_id");
    }

    public function getDataAllocationForTransaction($transaction, $allocation_type, $operator_id = null, $comment = '') {
        $tableSPT = $this->getTable('sales/payment_transaction');
        $select = $this->getReadConnection()->select();

        $select->from(array("pt" => $tableSPT), 'pt.order_id');
        $select->where('pt.transaction_id = ?', $transaction->getId());
        $select->where('pt.txn_status = ?',Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED);

        $ordersIDs = $this->getReadConnection()->fetchAll($select);

//        Mage::log("ordersIDs");
//        Mage::log($ordersIDs);

        $tablePo = $this->getTable("udpo/po");
        $select = $this->getReadConnection()->select();

        $select->from(array("po" => $tablePo));
        $select->where("po.order_id IN(?)", $ordersIDs);
        $select->joinLeft(array("order" => $this->getTable("sales/order")),
            "order.entity_id = po.order_id",
            "order.customer_is_guest");

        $poData = $this->getReadConnection()->fetchAll($select);

//        Mage::log("poData");
//        Mage::log($poData);
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
                'customer_id'       => ((!$po['customer_is_guest']) && !is_null($po['customer_is_guest'])) ? $po['customer_id'] : null
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
     *
     * @param $data
     */
    public function appendAllocations($data) {

        $writeConnection = $this->_getWriteAdapter();
        $writeConnection->insertMultiple($this->getTable('zolagopayment/allocation'), $data);

    }


}

