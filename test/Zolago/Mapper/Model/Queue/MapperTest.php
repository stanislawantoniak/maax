<?php

class Zolago_Mapper_Model_Queue_MapperTest extends ZolagoDb_TestCase {

    protected $_model;
    protected function _getModel() {
        if (empty($this->_model)) {
            $this->_model = Mage::getModel('zolagomapper/queue_mapper');
            $this->assertNotEmpty($this->_model);
        }
        return $this->_model;
    }
    protected function _setQuery($query) {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('zolagomapper_queue_item/mapper');
        $resource = $readConnection->query(sprintf($query,$table));
        $row = $resource->fetch();
        return $row;
    }
    protected function _checkQueue($cond = array()) {
        $tmp = array();
        foreach ($cond as $key=>$val) {
            $tmp[] = $key.' = \''.$val.'\'';
        }
        $query = 'SELECT * FROM %s ';
        if (count($tmp)) {
            $query .= ' WHERE '.implode(' and ',$tmp);
        }
        $query .= ' ORDER BY queue_id desc ';
        return $this->_setQuery($query);
    }
    protected function _checkQueueLength($status) {
        $query = 'SELECT count(*) as counter FROM %s WHERE status = \''.$status.'\'';
        return $this->_setQuery($query);
    }
    
    /**
     * add mapper for test
     */
    protected function _addMapper() {
        $mapper = Mage::getModel('zolagomapper/mapper');
        $data = array (
            'name' => 'testowy mapper',
            'is_active' => 1,
            'website_id' => 1,
            'attribute_set_id' => 1,
        );
        $mapper->setData($data);
        $mapper->save();                
    }
    /**
     * random product id
     */
    protected function _getMapperId() {
        $model = Mage::getModel('zolagomapper/mapper');
        $collection = $model->getCollection();
        $collection->setPageSize(1);
        $item = $collection->getFirstItem();
        $this->assertNotEmpty($item);
        $id = $item->getId();
        $this->assertNotEmpty($id);
        return $id;
    }
    public function testCreate() {
        $this->_getModel();
    }
    public function testInsert() {
        $this->_addMapper();
        $mapperId = $this->_getMapperId();
        $model = $this->_getModel();
        // check
        $return = $this->_checkQueue(array (
                                         'mapper_id' => $mapperId,
                                         'status' => 0
                                     ));
        if (!$return) {
            $model->push($mapperId);
            // check again
            $return = $this->_checkQueue(array (
                                             'mapper_id' => $mapperId,
                                             'status' => 0
                                         ));
            $this->assertNotEmpty($return);
        }

        // check difference
        $old = $return;
        $model->push($mapperId);
        // check again
        $return = $this->_checkQueue(array (
                                         'mapper_id' => $mapperId,
                                         'status' => 0
                                     ));
        $this->assertNotEquals($old,$return);
    }
    public function testProcess() {
        $this->_addMapper();
        $queue = $this->_getModel();
        $mapperId = $this->_getMapperId();
        // before push
        $row = $this->_checkQueueLength(0);
        $countNew = $row['counter'];
        $row = $this->_checkQueueLength(1);
        $countProcess = $row['counter'];
        
        $queue->push($mapperId);
        // check after push
        $row = $this->_checkQueueLength(1);
        $this->assertEquals($countProcess,$row['counter']);
        $row = $this->_checkQueueLength(0);
        $this->assertEquals($countNew+1,$row['counter']);

        $queue->process();
        // after process
        $row = $this->_checkQueueLength(0);
        $this->assertLessThanOrEqual($countNew,$row['counter']);

        $row = $this->_checkQueueLength(1);
        $this->assertGreaterThanOrEqual($countProcess+1,$row['counter']);
    }
}
