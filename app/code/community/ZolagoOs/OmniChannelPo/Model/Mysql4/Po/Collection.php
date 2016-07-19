<?php
/**
  
 */

class ZolagoOs_OmniChannelPo_Model_Mysql4_Po_Collection extends Mage_Sales_Model_Mysql4_Order_Collection_Abstract
{
    protected $_eventPrefix = 'udpo_po_collection';
    protected $_eventObject = 'po_collection';
    protected $_orderField = 'order_id';

    protected function _construct()
    {
        $this->_init('udpo/po');
    }

    public function afterLoad()
    {
        $this->_afterLoad();
        return $this;
    }
    protected function _afterLoad()
    {
        $this->walk('afterLoad');
        if (($stockPo = $this->getStockPo())) {
            foreach ($this->getItems() as $item) {
                $item->setStockPo($stockPo);
            }
        }
    }
    
    public function addPendingBatchStatusFilter()
    {
    	$exportOnPoStatus = Mage::getStoreConfig('zolagoos/batch/export_on_po_status');
    	if (!is_array($exportOnPoStatus)) {
    		$exportOnPoStatus = explode(',', $exportOnPoStatus);
    	}
        $this->getSelect()->where("main_table.udropship_status in (?)", $exportOnPoStatus);
        return $this;
    }
    public function addPendingStockpoBatchStatusFilter()
    {
    	$exportOnPoStatus = Mage::getStoreConfig('zolagoos/batch/export_on_stockpo_status');
    	if (!is_array($exportOnPoStatus)) {
    		$exportOnPoStatus = explode(',', $exportOnPoStatus);
    	}
        $this->getSelect()->where("main_table.udropship_status in (?)", $exportOnPoStatus);
        return $this;
    }

    public function addPendingStockpoFilter()
    {
    	$exportOnPoStatus = Mage::getStoreConfig('zolagoos/stockpo/generate_on_po_status');
    	if (!is_array($exportOnPoStatus)) {
    		$exportOnPoStatus = explode(',', $exportOnPoStatus);
    	}
        $this->getSelect()->where("main_table.udropship_status in (?)", $exportOnPoStatus);
        $this->getSelect()->where("ustockpo_id is null");
        return $this;
    }

    protected $_orderJoined=false;
    protected function _joinOrderTable()
    {
        if (!$this->_orderJoined) {
            $this->getSelect()->join(
                array('order_table'=>$this->getTable('sales/order')),
                'order_table.entity_id=main_table.order_id',
                array()
            );
            $this->_orderJoined = true;
        }
        return $this;
    }

    public function addOrderDateFilter($dateFilter)
    {
        $this->_joinOrderTable();
        $this->addFieldToFilter('order_table.created_at', $dateFilter);
        return $this;
    }


    public function addOrders()
    {
        if (!Mage::helper('udropship')->isSalesFlat()) {
            $this->addAttributeToSelect('*', 'inner');
        }

        $orderIds = array();
        foreach ($this as $po) {
            if ($po->getOrderId()) {
                $orderIds[$po->getOrderId()] = 1;
            }
        }

        if ($orderIds) {
            $orders = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in'=>array_keys($orderIds)));
            foreach ($this as $po) {
                $po->setOrder($orders->getItemById($po->getOrderId()));
            }
        }
        return $this;
    }

    public function addStockPos()
    {
        $this->addAttributeToSelect('*', 'inner');

        $stockPoIds = array();
        foreach ($this as $po) {
            if ($po->getUstockpoId()) {
                $stockPoIds[$po->getUstockpoId()] = 1;
            }
        }

        if ($stockPoIds) {
            $stockPos = Mage::getModel('ustockpo/po')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in'=>array_keys($stockPoIds)));
            foreach ($this as $po) {
                $po->setStockPo($stockPos->getItemById($po->getUstockpoId()));
            }
        }
        return $this;
    }

    protected $_stockPo;
    public function setStockPo($stockPo)
    {
        $this->_stockPo = $stockPo;
        return $this;
    }
    public function getStockPo()
    {
        return $this->_stockPo;
    }

    public function setStockPoFilter($stockPo)
    {
        if ($stockPo instanceof ZolagoOs_OmniChannelStockPo_Model_Po) {
            $this->setStockPo($stockPo);
            $stockPoId = $stockPo->getId();
            if ($stockPoId) {
                $this->addFieldToFilter('ustockpo_id', $stockPoId);
            } else {
                $this->_totalRecords = 0;
                $this->_setIsLoaded(true);
            }
        } else {
            $this->addFieldToFilter('ustockpo_id', $stockPo);
        }
        return $this;
    }
}
