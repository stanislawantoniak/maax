<?php

class ZolagoOs_OmniChannelVendorRatings_Model_Mysql4_Review extends Mage_Review_Model_Mysql4_Review
{
    public function getTotalReviews($entityPkValue, $approvedOnly=false, $storeId=0)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->_reviewTable, "COUNT(*)")
            ->where("{$this->_reviewTable}.entity_id = ?", Mage::helper('udratings')->useEt())
            ->where("{$this->_reviewTable}.entity_pk_value = ?", $entityPkValue);

        if($storeId > 0) {
            $select->join(array('store'=>$this->_reviewStoreTable),
                $this->_reviewTable.'.review_id=store.review_id AND store.store_id=' . (int)$storeId, array());
        }
        if( $approvedOnly ) {
            $select->where("{$this->_reviewTable}.status_id = ?", 1);
        }
        return $this->_getReadAdapter()->fetchOne($select);
    }
}