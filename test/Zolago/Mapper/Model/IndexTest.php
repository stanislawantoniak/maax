<?php
class Zolago_Mapper_Model_IndexTest extends ZolagoDb_TestCase {

    protected $_model;
    
    protected function _getModel() {
        if (!$this->_model) {
            $this->_model = Mage::getModel('zolagomapper/index');
            $this->assertNotEmpty($this->_model);
        }
        return $this->_model;
    }
    
    public function testCreate() {
        $model = $this->_getModel();
        $this->assertNotEmpty($model);
        
    }
}