<?php
/**
 * resource pos collection tester
 */
class Zolago_Pos_Model_Resource_Pos_CollectionTest extends Zolago_TestCase {

    protected $collection;

    protected function _getCollection() {
        if (!$this->collection) {
            $this->collection = Mage::getResourceModel('zolagopos/pos_collection');
        }
        return $this->collection;
    }
    /**
     * create object test
     */
    public function testCreate() {
        $collection = $this->_getCollection();
        $this->assertNotEmpty($collection);
    }

    /**
     * creating test pos
     */
    protected function _addPos($name,$vendor) {

        $pos = Mage::getModel('zolagopos/pos');

        $data = Zolago_Pos_Helper_Test::getPosData();
        $data['name'] = $name;
        // vendor settings
        $data['vendor_owner_id'] = $vendor->getId();
        $pos->setData($data);
        $pos->save();
    }
    /**
     * filter test
     */
    public function testFilter() {
        // add one pos (in transaction)
        $transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
        $transaction->beginTransaction();


        $posName = '_TestPosName_'.rand();
        $vendor = Zolago_Pos_Helper_Test::getVendor();
        $this->assertNotEquals($vendor->getId(),0);
        $this->_addPos($posName,$vendor);
        $vendor->load();
        $vendorName = $vendor->getName();
        // collection testing
        $collection = $this->_getCollection();
        $collection->setPageSize(1);
        $collection->addVendorOwnerName($vendorName);
        $collection->load();
        $this->assertEquals(count($collection),1);
        $item = $collection->getFirstItem();
        $this->assertNotEmpty($item->getId());
        


        $transaction->rollback();

    }

}