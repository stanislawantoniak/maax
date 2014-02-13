<?php
class Zolago_Pos_Model_Resource_Pos extends Zolago_TestCase {
    
    /**
     * new object test
     */
    public function testCreate() {	
        $obj = Mage::getModel('zolagopos_resource/pos');
        $this->assertEquals('Zolago_Pos_Model_Resource_Pos',get_class($obj));    
    }    
}
?>