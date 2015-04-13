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
            ->where('sharing_code = ?', $object->getSharingCode())
            ->orWhere('customer_id = ?', $object->getCustomerId());

        $rowAll = $select->query()->fetchAll();
        Mage::log($rowAll, null, 'mylog.log');

        $rowsUser    = array();
        $rowsVisitor = array();
        $commonPart  = array();

        foreach ($rowAll as $row) {
            if (!is_null($row['customer_id'])) {
                $rowsUser[] = $row;
                continue;
            }
            if (!is_null($row['sharing_code'])) {
                $rowsVisitor[] = $row;
                continue;
            }
        }

        //todo
        return $this;
    }
}
