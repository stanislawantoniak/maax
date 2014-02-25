<?php
/**
 * po helper test
 */
class Zolago_Po_Helper_DataTest extends Zolago_TestCase {

    protected $_transaction;

    public function __construct() {
        parent::__construct();
        $this->_transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
        $this->_transaction->beginTransaction();

    }
    /**
     * vendorPoCollection
     */
    public function testVendorPoCollection() {
        if (!no_coverage()) {
            $this->markTestSkipped('Coverage error');
        }
        // only vendor        
        $helper = Mage::helper('udpo');
        $this->assertNotEmpty($helper);
        $session = Mage::getSingleton('udropship/session');
        $session->setOperatorMode(false);
        $vendor = Zolago_Operator_Helper_Test::getVendor();
        $session->setVendorId($vendor->getId());
        $collection = $helper->getVendorPoCollection();
        // no exception
    }
    public function testVendorPoCollectionOperator() {
        if (!no_coverage()) {
            $this->markTestSkipped('Coverage error');
        }
        // operator without pos
        // second helper
        $helper = Mage::helper('udpo');
        $helper->setCondJoined(false);
        $vendor = Zolago_Operator_Helper_Test::getVendor();
        $data = Zolago_Operator_Helper_Test::getOperatorData();
        $data['vendor_id'] = $vendor->getId();
        $operator = Mage::getModel('zolagooperator/operator');
        $operator->setData($data);
        $operator->save();
        $session = Mage::getSingleton('udropship/session');
        $session->setOperator($operator);
        $session->setOperatorMode(true);
        $this->assertTrue($session->isOperatorMode());
        $collection = $helper->getVendorPoCollection();
        $this->assertEmpty(count($collection));
        
    }
    public function testVendorPoCollectionOperatorWithPos() {
        // operator with pos
        if (!no_coverage()) {
            $this->markTestSkipped('Coverage error');
        }

        // third helper helper
        $helper = Mage::helper('udpo');
        $helper->setCondJoined(false);
        // prepare pos
        $posmodel = Mage::getModel('zolagopos/pos');
        $data = Zolago_Pos_Helper_Test::getPosData();        
        $vendor = Zolago_Operator_Helper_Test::getVendor();
        $posmodel->setData($data);
        $posmodel->setPostVendorIds(array($vendor->getId()));
        $posmodel->save();
        $this->assertNotEmpty($posmodel);
        // prepare operator
        $data = Zolago_Operator_Helper_Test::getOperatorData();
        $data['vendor_id'] = $vendor->getId();
        $operator = Mage::getModel('zolagooperator/operator');
        $data['allowed_pos'] = array($posmodel->getId());
        $operator->setData($data);
        $operator->save();
        $this->assertNotEmpty($operator);
        // test

        $collection = $helper->getVendorPoCollection();
        // no exception        
    }
    public function __destruct() {
        $this->_transaction->rollback();
    }
}
