<?php

class ZolagoOs_OmniChannelVendorRatings_Model_Mysql4_Review_Shipment_Collection extends Mage_Sales_Model_Mysql4_Order_Shipment_Collection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_reviewStoreTable = Mage::getSingleton('core/resource')->getTableName('review/review_store');
    }
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->join(array('udv'=>$this->getTable('udropship/vendor')),
                   'main_table.udropship_vendor=udv.vendor_id and udv.allow_udratings>0',
                   array('vendor_name', 'vendor_email'=>'email'));
        return $this;
    }
    public function addCustomerFilter($customer)
    {
        $this->addFieldToFilter('main_table.customer_id', is_scalar($customer) ? $customer : $customer->getId());
        return $this;
    }
    public function addPendingFilter()
    {
        $reviewTable = Mage::getSingleton('core/resource')->getTableName('review/review');
        $this->getSelect()
            ->joinLeft(array('rt' => $reviewTable),
                'rt.rel_entity_pk_value = main_table.entity_id and rt.entity_id='.Mage::helper('udratings')->myEt(),
                array())
            ->where('rt.review_id is null');
        $readyStatuses = Mage::getStoreConfig('udropship/vendor_rating/ready_status');
        if (!is_array($readyStatuses)) {
            $readyStatuses = explode(',', $readyStatuses);
        }
        if (empty($readyStatuses)) {
            $this->getSelect()->where('false');
        } else {
            $this->getSelect()->where('main_table.udropship_status in (?)', $readyStatuses);
        }
        return $this;
    }
    public function addNotificationDaysFilter($filter)
    {
        if (!is_array($filter)) {
            $filter = explode(',', $filter);
        }
        $conn = $this->getSelect()->getAdapter();
        $cases = array();
        $now = now();
        $idx=0; foreach ($filter as $fPart) {
            $cases[$idx] = $conn->quoteInto('DATE_ADD(main_table.udrating_date, interval '.(int)$fPart.' DAY)<?', $now);
            $idx++;
        }
        $this->getSelect()->where(
            Mage::helper('udratings')->getCaseSql('main_table.udrating_emails_sent', $cases, 'false')
        );
        return $this;
    }
    public function getCustomerIds()
    {
        $clonedSelect = clone $this->getSelect();
        $clonedSelect->reset(Zend_Db_Select::COLUMNS);
        $clonedSelect->columns('main_table.customer_id')->distinct(true);
        $customerIds = $clonedSelect->getAdapter()->fetchCol($clonedSelect);
        return $customerIds;
    }
    public function joinReviews()
    {
        $reviewTable = Mage::getSingleton('core/resource')->getTableName('review/review');
        $reviewDetailTable = Mage::getSingleton('core/resource')->getTableName('review/review_detail');
        $this->getSelect()
            ->join(array('rt' => $reviewTable),
                'rt.rel_entity_pk_value = main_table.entity_id and rt.entity_id='.Mage::helper('udratings')->myEt(),
                array('review_id', 'created_at', 'entity_pk_value', 'rel_entity_pk_value', 'status_id'))
            ->join(array('rdt' => $reviewDetailTable), 'rdt.review_id = rt.review_id');
        return $this;
    }
    public function joinShipmentItemData()
    {
        $this->getSelect()
            ->join(array('ssi' => $this->getTable('sales/shipment_item')), 'ssi.parent_id = main_table.entity_id',array(''))
            ->join(array('soi' => $this->getTable('sales/order_item')), 'ssi.order_item_id = soi.item_id',array(''))
            ->where('soi.parent_item_id is null')
            ->group('main_table.entity_id');
        $this->getSelect()->columns(array(
            'product_name_list'=>new Zend_Db_Expr("group_concat(soi.name separator '\n')"),
            'product_sku_list'=>new Zend_Db_Expr("group_concat(soi.sku separator '\n')"),
        ));
    }
    public function addStoreFilter($storeId=null)
    {
        parent::addStoreFilter($storeId);
        $this->getSelect()
            ->join(array('store'=>$this->_reviewStoreTable),
                'rt.review_id=store.review_id AND store.store_id=' . (int)$storeId, array());
        return $this;
    }
    public function setStoreFilter($storeId)
    {
        if( is_array($storeId) && isset($storeId['eq']) ) {
            $storeId = array_shift($storeId);
        }

        if( is_array($storeId) ) {
            $this->getSelect()
                ->join(array('store'=>$this->_reviewStoreTable),
                    $this->getConnection()->quoteInto('rt.review_id=store.review_id AND store.store_id IN(?)', $storeId), array())
                ->distinct(true)
                ;
        } else {
            $this->getSelect()
                ->join(array('store'=>$this->_reviewStoreTable),
                    'rt.review_id=store.review_id AND store.store_id=' . (int)$storeId, array());
        }

        return $this;
    }
    public function addStoreData()
    {
        $this->_addStoreDataFlag = true;
        return $this;
    }
    public function addEntityFilter($entityId)
    {
        $this->getSelect()
            ->where('rt.entity_pk_value = ?', $entityId);
        return $this;
    }

    public function addStatusFilter($status)
    {
        $this->getSelect()
            ->where('rt.status_id = ?', $status);
        return $this;
    }

    public function setDateOrder($dir='DESC')
    {
        $this->setOrder('rt.created_at', $dir);
        return $this;
    }

    public function addReviewSummary()
    {
        foreach( $this->getItems() as $item ) {
            $model = Mage::getModel('rating/rating');
            $model->getReviewSummary($item->getReviewId());
            $item->addData($model->getData());
        }
        return $this;
    }

    public function addRateVotes()
    {
        foreach( $this->getItems() as $item ) {
            $votesCollection = Mage::getModel('rating/rating_option_vote')
                ->getResourceCollection()
                ->setEntityPkFilter($item->getEntityId())
                ->setStoreFilter(Mage::app()->getStore()->getId())
                ->load();
            $item->setRatingVotes( $votesCollection );
        }
        return $this;
    }

    public function setOrder($attribute, $dir='desc')
    {
        switch( $attribute ) {
            case 'rt.review_id':
            case 'rt.created_at':
            case 'rt.status_id':
            case 'rdt.title':
            case 'rdt.nickname':
            case 'rdt.detail':
                $this->getSelect()->order($attribute . ' ' . $dir);
                break;
            case 'stores':
                // No way to sort
                break;
            case 'type':
                $this->getSelect()->order('rdt.customer_id ' . $dir);
                break;
            default:
                parent::setOrder($attribute, $dir);
        }
        return $this;
    }

    protected $_addStoreDataFlag;
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->_addStoreDataFlag) {
            $this->_addStoreData();
        }
        return $this;
    }

    protected function _addStoreData()
    {
        $reviewsIds = $this->getColumnValues('review_id');
        $storesToReviews = array();
        if (count($reviewsIds)>0) {
            $select = $this->getConnection()->select()
                ->from($this->_reviewStoreTable)
                ->where('review_id IN(?)', $reviewsIds)
                ->where('store_id > ?', 0);
            $result = $this->getConnection()->fetchAll($select);
            foreach ($result as $row) {
                if (!isset($storesToReviews[$row['review_id']])) {
                    $storesToReviews[$row['review_id']] = array();
                }
                $storesToReviews[$row['review_id']][] = $row['store_id'];
            }
        }

        foreach ($this as $item) {
            if(isset($storesToReviews[$item->getReviewId()])) {
                $item->setData('stores',$storesToReviews[$item->getReviewId()]);
            } else {
                $item->setData('stores', array());
            }

        }
    }
}