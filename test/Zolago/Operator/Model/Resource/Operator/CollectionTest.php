<?php
/**
 * operator collection test
 */
class Zolago_Operator_Model_Resource_Operator_CollectionTest extends Zolago_TestCase {

    protected $_transaction;
    public function __construct() {
        parent::__construct();
        $this->_transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
        $this->_transaction->beginTransaction();        
    }

    public function __destruct() {
        $this->_transaction->rollback();
    }

    /**
     * filter test
     */
    public function testFilter() {
        $model = Zolago_Operator_Helper_Test::getVendor();
        $this->assertNotEmpty($model);
        $this->assertNotEmpty($model->getId());
        $data = Zolago_Operator_Helper_Test::getOperatorData();
        
        $operator = Mage::getModel('zolagooperator/operator');
        $data['vendor_id'] = $model->getId();
        $data['email'] = 'pimpekzlasu-collection@vupe.pl';
        $operator->setData($data);
        $operator->save();
        
        $collection = $operator->getCollection();
        $collection->addActiveFilter();
        $collection->addLoginFilter('pimpekzlasu-collection@vupe.pl');
        $collection->addVendorFilter($model);
        $this->assertEquals(1,count($collection),print_R($collection->getData(),1));
        $item = $collection->getFirstItem();
        $this->assertEquals($operator->getId(),$item->getId());
        
        // new collection
        $collection = $operator->getCollection();
        $collection->addLoginFilter('pimpekzlasu@vupe.pl-adlfjalsdfamlk wrong');
        $this->assertEquals(0,count($collection));
    }
}