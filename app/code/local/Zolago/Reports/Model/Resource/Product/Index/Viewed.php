<?php

class Zolago_Reports_Model_Resource_Product_Index_Viewed extends Mage_Reports_Model_Resource_Product_Index_Viewed
{
    /**
     * Update Customer from visitor (Customer logged in)
     *
     * @param Mage_Reports_Model_Product_Index_Abstract $object
     * @return Zolago_Reports_Model_Resource_Product_Index_Viewed
     */
    public function updateCustomerFromVisitor(Mage_Reports_Model_Product_Index_Abstract $object)
    {
        /** @var Zolago_Reports_Model_Product_Index_Viewed $object */

        /**
         * Do nothing if customer not logged in
         */
        if (!$object->getCustomerId() || !$object->getSharingCode()) {
            return $this;
        }

        /**
         * Gets all rows somehow connected with user
         */
        $adapter = $this->_getWriteAdapter();
        $select  = $adapter->select()
            ->from($this->getMainTable())
            ->where( 'sharing_code = ?', $object->getSharingCode())
            ->orWhere('customer_id = ?', $object->getCustomerId());

        $rowAll = $select->query()->fetchAll();

        // Help array
        $allRowsByProdId = array();
        foreach ($rowAll as $row) {
            $allRowsByProdId[$row['product_id']][] = $row ;
        }

        // Ids (index_id) to remove because DB index can't duplicate
        $idsToRemove = array();
        // Records that need to be updated (newer date added_at)
        $rowsToUpdateAddedAt = array();

        foreach ($allRowsByProdId as $row) {
            if (count($row) > 1) {
                $newDate0 = DateTime::createFromFormat('Y-m-d H:i:s', $row[0]['added_at']);
                $newDate1 = DateTime::createFromFormat('Y-m-d H:i:s', $row[1]['added_at']);
                $newestDate = $newDate0 > $newDate1 ? $newDate0 : $newDate1;
                foreach ($row as $r) {
                    if (is_null($r['customer_id'])) {
                        $idsToRemove[] = $r['index_id'];
                    } else {
                        if (DateTime::createFromFormat('Y-m-d H:i:s', $r['added_at']) < $newestDate) {
                            $rowsToUpdateAddedAt[$r['index_id']] = $r;
                            $rowsToUpdateAddedAt[$r['index_id']]['added_at'] = $newestDate->format('Y-m-d H:i:s');
                        }
                    }
                }
            }
        }

        // Removing from DB
        if (!empty($idsToRemove)) {
            $adapter->delete($this->getMainTable(), 'index_id IN (' .implode(",",$idsToRemove).")");
        }

        // Transfer info from visitor to customer
        $adapter->update(
            $this->getMainTable()
            ,array(
                'customer_id'   => $object->getCustomerId(),
                'sharing_code'  => null
            )
            ,"sharing_code = '" . $object->getSharingCode() ."'"
        );

        // Updating date added_at
        foreach ($rowsToUpdateAddedAt as $key => $row) {
            $adapter->update(
                $this->getMainTable()
                ,array(
                    'customer_id'   => $object->getCustomerId(),
                    'store_id'      => $object->getStoreId(),
                    'added_at'      => $row['added_at']
                )
                ,'index_id = ' . $key
            );
        }

        return $this;
    }

    /**
     * Purge visitor data by customer (logout)
     *
     * @param Mage_Reports_Model_Product_Index_Abstract $object
     * @return Zolago_Reports_Model_Resource_Product_Index_Viewed
     */
    public function purgeVisitorByCustomer(Mage_Reports_Model_Product_Index_Abstract $object)
    {
        return $this;
    }

    /**
     * Save Product Index data (forced save)
     *
     * @param Mage_Core_Model_Abstract|Mage_Reports_Model_Product_Index_Abstract $object
     * @return Zolago_Reports_Model_Resource_Product_Index_Viewed
     */
    public function save(Mage_Core_Model_Abstract $object)
    {
        return parent::save($object);
    }

    /**
     * Clean index (visitor)
     *
     * @return Mage_Reports_Model_Resource_Product_Index_Abstract
     */
    public function clean()
    {
        while (true) {
            $select = $this->_getReadAdapter()->select()
                ->from(array('main_table' => $this->getMainTable()), array($this->getIdFieldName()))
                ->joinLeft(
                    array('wishlist' => $this->getTable('wishlist/wishlist')),
                    'main_table.sharing_code = wishlist.sharing_code',
                    array())
                ->where('main_table.sharing_code IS NOT NULL')
                ->where('wishlist.sharing_code IS NULL')
                ->limit(100);
            $indexIds = $this->_getReadAdapter()->fetchCol($select);

            if (!$indexIds) {
                break;
            }

            $this->_getWriteAdapter()->delete(
                $this->getMainTable(),
                $this->_getWriteAdapter()->quoteInto($this->getIdFieldName() . ' IN(?)', $indexIds)
            );
        }
        return $this;
    }

    /**
     * Add information about product ids to visitor/customer
     *
     *
     * @param Varien_Object|Zolago_Reports_Model_Product_Index_Viewed $object
     * @param array $productIds
     * @return Zolago_Reports_Model_Resource_Product_Index_Viewed
     */
    public function registerIds(Varien_Object $object, $productIds)
    {
        $row = array(
            'sharing_code'    => $object->getSharingCode(),
            'customer_id'   => $object->getCustomerId(),
            'store_id'      => $object->getStoreId(),
        );
        $addedAt = date('Y-m-d H:i:s', Mage::getSingleton('core/data')->timestamp());
        $data = array();
        foreach ($productIds as $productId) {
            $productId = (int) $productId;
            if ($productId) {
                $row['product_id'] = $productId;
                $row['added_at']   = $addedAt->format('Y-m-d H:i:s');
                $data[] = $row;
            }
            $addedAt -= ($addedAt > 0) ? 1 : 0;
        }

        $matchFields = array('product_id', 'store_id');
        foreach ($data as $row) {
            Mage::getResourceHelper('reports')->mergeVisitorProductIndex(
                $this->getMainTable(),
                $row,
                $matchFields
            );
        }
        return $this;
    }
}
