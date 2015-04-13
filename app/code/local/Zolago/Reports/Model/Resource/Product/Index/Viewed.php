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
}
