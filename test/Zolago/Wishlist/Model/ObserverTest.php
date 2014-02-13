<?php
/**
 *  Wishlist observer tests
 */
class Foo  {
    public function getItems() {	
        return array();
    }
    public function getItem() {	
        return 'barItem';
    }
    public function getProductId() {
        return 0;
    }
}
class Zolago_Wishlist_Model_ObserverTest extends Zolago_TestCase {

    /**
     * get tested object
     */
    protected function _getObserver() {
        if (!$this->observer) {
            $this->observer = Mage::getSingleton('zolagowishlist/observer');
        };
        return $this->observer;
    }
    /**
     * get existing product id
     */
    protected function _getFirstProductId() {
        if (!$this->productId) {
            $model = Mage::getModel('catalog/product');
            $collection = $model->getCollection();
            $collection->setPageSize(1);
            $collection->load();
            $product = $collection->getFirstItem();
            $productId = $product->getId();
            $this->assertNotEmpty($productId);
            $this->productId = $productId;
        }
        return $this->productId;
    }
    /**
     * constructor test
     */
    public function testCreate() {
        $x = $this->_getObserver();
        $this->assertEquals(get_class($x),'Zolago_Wishlist_Model_Observer');
        $this->_createEvent();
    }

    /**
     * creating fake event object
     */
    protected function _createEvent($empty = true) {
        $productId = $this->_getFirstProductId();
        if ($empty) {
            $event = $this->getMock('Foo');
            $event->expects($this->any())
                ->method('getItems')
                ->will($this->returnValue(array()));
            $event->expects($this->any())
                ->method('getItems')
                ->will($this->returnValue(null));
        } else {
            $item = $this->getMock('Foo');
            $item->expects($this->any())
                ->method('getProductId')
                ->will($this->returnValue($productId));
            $event = $this->getMock('Foo');
            $event->expects($this->any())
                ->method('getItems')
                ->will($this->returnValue(array($item)));
            $event->expects($this->any())
                ->method('getItem')
                ->will($this->returnValue($item));
        }
        return $event;
    }
    
    /**
     * getting controlled object     
     */
    protected function _getValueControlledObject() {
        $productId = $this->_getFirstProductId();
        return Mage::getResourceModel('catalog/product')
            ->getAttributeRawValue($productId, 'wishlist_count', 0); }
    
    /**
     * single test
     */
    protected function _singleTest($fakeEvent,$expectedValue,$function) {
        $observer = $this->_getObserver();
        $observer->$function($fakeEvent);
        $value = $this->_getValueControlledObject();
        $this->assertEquals($expectedValue,$value);
    }
    /**
     * add and delete from wishlist - empty event
     */
    public function testWishlistEmptyOperations() {
        $fake = $this->_createEvent();
        $beginValue = $this->_getValueControlledObject();
        $this->_singleTest($fake,$beginValue,'wishlistAddAfter');
        $this->_singleTest($fake,$beginValue,'wishlistDelAfter');
    }
    
    /**
     * add and delete from wishlist - event not empty
     */
    public function testWishlistFullOperations() {
        $beginValue = $this->_getValueControlledObject();
        $fake = $this->_createEvent(false);
        $this->_singleTest($fake,$beginValue+1,'wishlistAddAfter');        
        $this->_singleTest($fake,$beginValue,'wishlistDelAfter');
    }
    
    /**
     * delete from wishlist where wishlist_count is 0
     */
    public function testWishlistLessThanZero() {
        $fake = $this->_createEvent(false);
        $beginValue = $this->_getValueControlledObject();
        $productId = $this->_getFirstProductId();
        // set 0 for test
        Mage::getSingleton('catalog/product_action')
            ->updateAttributes(array($productId),array ('wishlist_count'=>2),0);
        $this->_singleTest($fake,1,'wishlistDelAfter'); // change to 1
        $this->_singleTest($fake,0,'wishlistDelAfter'); // change to 0
        $this->_singleTest($fake,0,'wishlistDelAfter'); // less than 0 - no changes
        // end tests
        Mage::getSingleton('catalog/product_action')
            ->updateAttributes(array($productId),array ('wishlist_count'=>$beginValue),0);
    }
}

?>
