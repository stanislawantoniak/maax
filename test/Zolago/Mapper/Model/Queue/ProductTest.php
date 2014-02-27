<?php

class Zolago_Mapper_Model_Queue_ProductTest extends Zolago_TestCase {

    protected $_model;
    protected function _getModel() {
        if (empty($this->_model)) {
            $this->_model = Mage::getModel('zolagomapper/queue_product');
            $this->assertNotEmpty($this->_model);
        }
        return $this->_model;
    }
    protected function _checkQueue($cond = array()) {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('zolagomapper_queue_item/product');
        $tmp = array();
        foreach ($cond as $key=>$val) {
            $tmp[] = $key.' = \''.$val.'\'';
        }
        $query = 'SELECT * FROM '.$table;
        if (count($tmp)) {
            $query .= ' WHERE '.implode(' and ',$tmp);
        }
        $query .= ' ORDER BY queue_id desc ';
        $resource = $readConnection->query($query);
        $row = $resource->fetch();
        return $row;
    }
    /**
     * random product id
     */
    protected function _getProductId() {
        $model = Mage::getModel('catalog/product');
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
        $productId = $this->_getProductId();
        $model = $this->_getModel();
        // check
        $return = $this->_checkQueue(array (
                                         'product_id' => $productId,
                                         'status' => 0
                                     ));
        if (!$return) {
            $model->push($productId);
            // check again
            $return = $this->_checkQueue(array (
                                             'product_id' => $productId,
                                             'status' => 0
                                         ));
            $this->assertNotEmpty($return);
        }

        // check difference
        $old = $return;
        $model->push($productId);
        // check again
        $return = $this->_checkQueue(array (
                                         'product_id' => $productId,
                                         'status' => 0
                                     ));
        $this->assertNotEquals($old,$return);
    }
    public function testProcess() {
        $queue = $this->_getModel();
        $queue->process();
        $this->assertTrue(true);
    }
}
?>