<?php
class Zolago_Operator_Model_OperatorTest extends Zolago_TestCase {
    
    
    protected $_model;
    
    protected function _getModel() {
        if (empty($this->_model)) {
            $this->_model = Mage::getModel('zolagooperator/operator');
        }
        return $this->_model;
    }
    /**
     * new object test
     */
     public function testCreate() {
         $obj = $this->_getModel();
         $this->assertEquals('Zolago_Operator_Model_Operator',get_class($obj));
     }
     
    /**
     * region test
     */
    
    public function testGetVendor() {
        $transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
        $transaction->beginTransaction();
        
        $obj = $this->_getModel();
        $this->assertNotNull($obj->getVendor());
        $transaction->rollback();
    }
    
    
         
}
?>