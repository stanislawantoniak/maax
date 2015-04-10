<?php

class Zolago_Reports_Model_Resource_Product_Index_Viewed extends Mage_Reports_Model_Resource_Product_Index_Viewed
{
    /**
     * Update Customer from visitor (Customer logged in)
     *
     * @param Mage_Reports_Model_Product_Index_Abstract $object
     * @return Mage_Reports_Model_Resource_Product_Index_Abstract
     */
    public function updateCustomerFromVisitor(Mage_Reports_Model_Product_Index_Abstract $object)
    {
        /** @var Zolago_Reports_Model_Product_Index_Viewed $object */
//        Mage::log(__METHOD__ . '(' . __LINE__ . ')', null, 'mylog.log');
//        Mage::log($object->getCustomerId(), null, 'mylog.log');
//        Mage::log($object->getSharingCode(), null, 'mylog.log');
//        Mage::log($object->getVisitorId(), null, 'mylog.log');

        /**
         * Do nothing if customer not logged in
         */
        if (!$object->getCustomerId() || !$object->getSharingCode()) {
            return $this;
        }
        $adapter = $this->_getWriteAdapter();
        $select  = $adapter->select()
            ->from($this->getMainTable())
            ->where('sharing_code = ?', $object->getSharingCode());

        $rowSet = $select->query()->fetchAll();
        foreach ($rowSet as $row) {

            /* We need to determine if there are rows with known
               customer for current product.
             */

            $select = $adapter->select()
                ->from($this->getMainTable())
                ->where('customer_id = ?', $object->getCustomerId())
                ->where('product_id = ?', $row['product_id']);
            $idx = $adapter->fetchRow($select);

            $addedAt = date('Y-m-d H:i:s', Mage::getSingleton('core/data')->timestamp());

            if ($idx) {
                /* If we are here it means that we have two rows: one with known customer, but second just sharing_code (old visitor) is set
                 * One row should be updated with customer_id, second should be deleted
                 *
                 */
                $adapter->delete($this->getMainTable(), array('index_id = ?' => $row['index_id']));
                $where = array('index_id = ?' => $idx['index_id']);
                $data  = array(
                    'sharing_code'  => $object->getSharingCode(),
                    'store_id'      => $object->getStoreId(),
                    'added_at'      => $addedAt,
                );
            } else {
                $where = array('index_id = ?' => $row['index_id']);
                $data  = array(
                    'customer_id'   => $object->getCustomerId(),
                    'store_id'      => $object->getStoreId(),
                    'added_at'      => $addedAt
                );
            }

            $adapter->update($this->getMainTable(), $data, $where);

        }
        return $this;
    }
}
