<?php
/**
 * abstract class for queues
 */
abstract class Zolago_Common_Model_Queue_Abstract extends Mage_Core_Model_Abstract {
    
     protected $_limit = 1000;
     protected $_collection;
   
     abstract protected function _getItem();
   
     public function push($itemId) {
          $model = $this->_getItem();
          $model->setItemId($itemId);                    
          
          $model->save();
     }
    protected function _getCollection() {
          $model = $this->_getItem();
          $collection = $model->getCollection();
          $collection->setPageSize($limit);
          $collection->addFilter('status = 0');
          $collection->setOrder('insert_date');
          $this->_collection = $collection;
    }
    
    
    /**
     * change status after get
     */
     protected function _setLockRecords() {
          foreach ($this->_collection as $item) {
               $item->setStatus(-1);
               $item->setProcessDate(Varien_Date::now());          
          }
          $this->_collection->save();
     }
     
    /**
     * changing status after execute
     */     
     protected function _setDoneRecords() {
          foreach ($this->_collection as $item) {
               $item->setStatus(1);
          }
          $this->_collection->save();
     }
    /**
     * processing queue
     */
     public function process($limit = 0) {
          $limit = $limit? $limit:$this->_limit;
          $this->_getCollection();
          $this->_setLockRecords();
          $this->_execute();
          $this->_setDoneRecords();
     }
     abstract protected function _execute() {
     }
}
 
