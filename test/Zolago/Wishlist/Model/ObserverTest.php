<?php
/**
 *  Wishlist observer tests
 */
class Zolago_Wishlist_Model_ObserverTest extends Zolago_TestCase {
    public function testCreate() {	
        $x = Mage::getSingleton('zolagowishlist/observer');                
        $this->assertEquals(get_class($x),'Zolago_Wishlist_Model_Observer');
    }
}

?>
