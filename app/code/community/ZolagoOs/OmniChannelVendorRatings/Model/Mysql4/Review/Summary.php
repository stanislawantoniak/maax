<?php

class ZolagoOs_OmniChannelVendorRatings_Model_Mysql4_Review_Summary extends Mage_Review_Model_Mysql4_Review_Summary
{
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
           $select->where('entity_type=?',Mage::helper('udratings')->useEt());
        return $select;
    }
    public function reAggregate($summary)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from($this->getMainTable())
            ->where('entity_type=?',Mage::helper('udratings')->useEt())
            ->group(array('entity_pk_value', 'store_id'));
        foreach ($this->_getWriteAdapter()->fetchAll($select) as $row) {
            if (isset($summary[$row['store_id']]) && isset($summary[$row['store_id']][$row['entity_pk_value']])) {
                $summaryItem = $summary[$row['store_id']][$row['entity_pk_value']];
                if ($summaryItem->getCount()) {
                    $ratingSummary = round($summaryItem->getSum() / $summaryItem->getCount());
                } else {
                    $ratingSummary = $summaryItem->getSum();
                }
            } else {
                $ratingSummary = 0;
            }
            $this->_getWriteAdapter()->update(
                $this->getMainTable(),
                array('rating_summary' => $ratingSummary),
                $this->_getWriteAdapter()->quoteInto('primary_id = ?', $row['primary_id'])
            );
        }
        return $this;
    }
}