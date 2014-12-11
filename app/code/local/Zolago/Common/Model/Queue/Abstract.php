<?php
/**
 * abstract class for queues
 */
abstract class Zolago_Common_Model_Queue_Abstract extends Mage_Core_Model_Abstract {
    
     protected $_limit = 1000;
     protected $_collection;
   
     abstract protected function _getItem();

    /**
     * push into queue
     * @param mixed $itemId 
     */   
     public function push($itemId) {
          $model = $this->_getItem();
          $model->setItemId($itemId);                              
          $model->save();
     }
    protected function _getCollection() {
          $limit = $this->_limit;
          $model = $this->_getItem();
          $collection = $model->getCollection();
          $select = $collection->getSelect();
          $select->limit($limit);
          $select->where('status = ?',0);
          $select->order('insert_date');
          $this->_collection = $collection;
    }
    
    
     
    /**
     * processing queue
     */
     public function process($limit = 0) {
          if ($limit) {
              $this->_limit = $limit;
          }
          $this->_getCollection();
          if (!count($this->_collection)) { 
              // empty queue
              return 0;
          }
          $this->_collection->setLockRecords();
          $this->_execute();
          $this->_collection->setDoneRecords();
          return count($this->_collection);
     }
     abstract protected function _execute();
}
 
