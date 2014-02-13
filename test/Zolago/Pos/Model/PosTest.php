<?php
class Zolago_Pos_Model_PosTest extends Zolago_TestCase {
    
    /**
     * new object test
     */
     public function testCreate() {
         $obj = Mage::getModel('zolagopos/pos');
         $this->assertEquals('Zolago_Pos_Model_Pos',get_class($obj));
     }
}
?>