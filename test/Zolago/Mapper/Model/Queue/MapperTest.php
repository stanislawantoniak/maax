<?php

class Zolago_Mapper_Model_Queue_MapperTest extends Zolago_TestCase {

    protected $_model;
    protected function _getModel() { 
        if (empty($this->_model)) {
            $this->_model = Mage::getModel('zolagomapper/queue_mapper');
            $this->assertNotEmpty($this->_model);
        }
        return $this->_model;
    }
    protected function _checkQueue($cond = array()) {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('zolagomapper/queue_mapper');
        $tmp = array();
        foreach ($cond as $key=>$val) {
            $tmp[] = $key.' = \''.$val.'\''; 
        }
        $query = 'SELECT * FROM '.$table;
        if (count($tmp)) {
            $query .= ' WHERE '.implode(' and ',$tmp);
        }
        return $readConnection->fetch($query);
        
            
    }
    /**
     * random product id
     */
    protected function _getMapperId() {    
        $model = Mage::getModel('zolagomapper');
        $collection = $model->getCollection();
        $collection->setPageSize(1);
        $item = $collection->getFirstItem();
        $this->assertEmpty($item);
        $id = $item->getId();
        $this->assertEmpty($id);
        return $id;
    }
    public function testCreate() {
        return;
        $this->_getModel();
    }    
    public function testInsert() {
        return;
        $mapperId = $this->_getMapperId();
        $model = $this->_getModel();
        $model->push($mapperId);
        // check
        $return = $this->_checkQueue(array (
            'mapper_id' => $mapperId,
            'status' => 0
        ));
        $this->assertEmpty($return,print_R($return,1));
                
    }
}
?>