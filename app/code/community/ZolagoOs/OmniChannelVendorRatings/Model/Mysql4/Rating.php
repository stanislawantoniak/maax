<?php

class ZolagoOs_OmniChannelVendorRatings_Model_Mysql4_Rating extends Mage_Rating_Model_Mysql4_Rating
{
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(array(
            'field' => array('rating_code','entity_id'),
            'title' => /* Mage::helper('rating')->__('Rating with the same title')*/ ''
        ));
        return $this;
    }
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if (Mage::helper('udropship')->compareMageVer('1.6.0.0','1.11.0.0', '<')) {
            $select->columns('main.is_aggregate');
        }
        return $select;
    }
    protected function _afterDelete(Mage_Core_Model_Abstract $object)
    {
        Mage::helper('udratings')->useEt($object->getEntityId());
        parent::_afterDelete($object);
        Mage::helper('udratings')->resetEt();
        return $this;
    }
    protected function _getEntitySummaryData($object)
    {
        $read = $this->_getReadAdapter();
        $sql = "SELECT
                    {$this->getTable('rating/rating_option_vote')}.entity_pk_value as entity_pk_value,
                    SUM({$this->getTable('rating/rating_option_vote')}.percent) as sum,
                    COUNT(*) as count,
                    {$this->getTable('review/review_store')}.store_id
                FROM
                    {$this->getTable('rating/rating_option_vote')}
                INNER JOIN
                    {$this->getTable('review/review')}
                    ON {$this->getTable('rating/rating_option_vote')}.review_id={$this->getTable('review/review')}.review_id
                LEFT JOIN
                    {$this->getTable('review/review_store')}
                    ON {$this->getTable('rating/rating_option_vote')}.review_id={$this->getTable('review/review_store')}.review_id
                INNER JOIN
                    {$this->getTable('rating/rating_store')} AS rst
                    ON rst.rating_id = {$this->getTable('rating/rating_option_vote')}.rating_id AND rst.store_id = {$this->getTable('review/review_store')}.store_id
                INNER JOIN
                    {$this->getTable('review/review_status')} AS review_status
                    ON {$this->getTable('review/review')}.status_id = review_status.status_id
                INNER JOIN
                    {$this->getTable('rating/rating')} AS rt
                    ON rt.rating_id = {$this->getTable('rating/rating_option_vote')}.rating_id AND rt.is_aggregate=1
                WHERE ";
        if ($object->getEntityPkValue()) {
            $sql .= "{$read->quoteInto($this->getTable('rating/rating_option_vote').'.entity_pk_value=?', $object->getEntityPkValue())} AND ";
        }
        $sql .= $read->quoteInto("{$this->getTable('review/review')}.entity_id = ? AND ", Mage::helper('udratings')->useEt());
        $sql .= "review_status.status_code = 'approved'
                GROUP BY
                    {$this->getTable('rating/rating_option_vote')}.entity_pk_value, {$this->getTable('review/review_store')}.store_id";

        return $read->fetchAll($sql);
    }
    public function getReviewSummary($object, $onlyForCurrentStore = true)
    {
        $read = $this->_getReadAdapter();
        $sql = "SELECT
                    SUM({$this->getTable('rating/rating_option_vote')}.percent) as sum,
                    COUNT(*) as count,
                    {$this->getTable('review/review_store')}.store_id
                FROM
                    {$this->getTable('rating/rating_option_vote')}
                LEFT JOIN
                    {$this->getTable('review/review_store')}
                    ON {$this->getTable('rating/rating_option_vote')}.review_id={$this->getTable('review/review_store')}.review_id
                INNER JOIN
                    {$this->getTable('rating/rating_store')} AS rst
                    ON rst.rating_id = {$this->getTable('rating/rating_option_vote')}.rating_id AND rst.store_id = {$this->getTable('review/review_store')}.store_id
                INNER JOIN
                    {$this->getTable('rating/rating')} AS rt
                    ON rt.rating_id = {$this->getTable('rating/rating_option_vote')}.rating_id AND rt.is_aggregate=1
                WHERE
                    {$read->quoteInto($this->getTable('rating/rating_option_vote').'.review_id=?', $object->getReviewId())}
                GROUP BY
                    {$this->getTable('rating/rating_option_vote')}.review_id, {$this->getTable('review/review_store')}.store_id";

        $data = $read->fetchAll($sql);

        if($onlyForCurrentStore) {
            foreach ($data as $row) {
                if($row['store_id']==Mage::app()->getStore()->getId()) {
                    $object->addData( $row );
                }
            }
            return $object;
        }

        $result = array();

        $stores = Mage::app()->getStore()->getResourceCollection()->load();

        foreach ($data as $row) {
            $clone = clone $object;
            $clone->addData( $row );
            $result[$clone->getStoreId()] = $clone;
        }

        $usedStoresId = array_keys($result);

        foreach ($stores as $store) {
               if (!in_array($store->getId(), $usedStoresId)) {
                   $clone = clone $object;
                $clone->setCount(0);
                $clone->setSum(0);
                $clone->setStoreId($store->getId());
                $result[$store->getId()] = $clone;

               }
        }

        return array_values($result);
    }
}