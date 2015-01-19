<?php

class Zolago_Payment_Model_Resource_Allocation extends Mage_Core_Model_Resource_Db_Abstract {

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('zolagopayment/allocation', "allocation_id");
    }

    public function getDataAllocationForTransaction($transaction_id, $allocation_type, $operator_id = null, $comment = '') {
        $tableSPT = $this->getTable('sales/payment_transaction');
        $select = $this->getReadConnection()->select();

        $select->from(array("pt" => $tableSPT), 'pt.order_id');
        $select->where('pt.transaction_id = ?', $transaction_id);
        $select->where('pt.txn_status = ?',Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED);

        $ordersIDs = $this->getReadConnection()->fetchAll($select);

//        Mage::log("ordersIDs");
//        Mage::log($ordersIDs);

        $tablePo = $this->getTable("udpo/po");
        $select = $this->getReadConnection()->select();

        $select->from(array("po" => $tablePo));
        $select->where("po.order_id IN(?)", $ordersIDs);
        $poData = $this->getReadConnection()->fetchAll($select);

//        Mage::log("poData");
//        Mage::log($poData);

        foreach ($poData as $po) {
            $data[] = array(
                'transaction_id'    => $transaction_id,
                'po_id'             => $po['entity_id'],
                'allocation_amount' => $po['grand_total_incl_tax'],
                'allocation_type'   => $allocation_type,
                'operator_id'       => $operator_id,
                'created_at'        => Mage::getSingleton('core/date')->gmtDate(),
                'comment'           => $comment,
                'customer_id'       => $po['customer_id']
            );
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

