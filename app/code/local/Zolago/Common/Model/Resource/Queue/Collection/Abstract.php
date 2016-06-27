<?php
/**
 * abstract for queue items collection
 */
class Zolago_Common_Model_Resource_Queue_Collection_Abstract
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
    
    protected $_lockedRecords; 
    
    protected function _construct() {   
        $this->_init($this->_tableName);
    }
 
    protected function _update($bind) { 
        $ids = $this->_lockedRecords;
        if (!$ids) {
            return;
        }
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table = $this->getTable($this->_tableName);
        $where = ' queue_id in (\''.implode('\',\'',$ids).'\')';
 
        $connection->update($table,$bind,$where);
        
    }
    /**
     * mark records as locked
     */
     
    public function setLockRecords() {
        $records = $this->toArray(array('queue_id'));            
        $out = array();
        if (!empty($records['items'])) {
            foreach ($records['items'] as $val) {
                $out[$val['queue_id']] = $val['queue_id'];
            }
        }
        $this->_lockedRecords = $out;
        $bind = array (
            'status' => -1
        );
        $this->_update($bind);
    }
    
    /**
     * set records status as done
     */
     public function setDoneRecords() {
         $bind = array (
             'status' => 1,
             'process_date' => Varien_Date::now(),
         );
         $this->_update($bind);
     }
}
