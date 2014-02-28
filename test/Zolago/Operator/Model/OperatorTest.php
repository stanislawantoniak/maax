<?php
class Zolago_Operator_Model_OperatorTest extends Zolago_TestCase {


    protected $_model;
    protected $_transaction;
    public function __construct() {
        parent::__construct();
        $this->_transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
        $this->_transaction->beginTransaction();
        $this->_testData = Zolago_Operator_Helper_Test::getOperatorData();

    }
    public function __destruct() {
        $this->_transaction->rollback();
    }

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

        $obj = $this->_getModel();
        $this->assertNotNull($obj->getVendor());
    }

    /**
     * test create save and delete
     */
    public function testSave() {

        $obj = $this->_getModel();
        // validator test
        $this->_validateTest('firstname',str_repeat('ab',100),Mage::helper('zolagooperator')->__('Max length of First name is 100'));
        $this->_validateTest('lastname',str_repeat('ab',100),Mage::helper('zolagooperator')->__('Max length of Last name is 100'));
        $this->_validateTest('is_active',null,Mage::helper('zolagooperator')->__('Is active is required'));
        $this->_validateTest('email',null,Mage::helper('zolagooperator')->__('Email is required'));
        $this->_validateTest('email',str_repeat('ab',100),Mage::helper('zolagooperator')->__('Max length of Email is 100'));
        $this->_validateTest('email','bleblesreble',Mage::helper('zolagooperator')->__('Email address is not valid'));


        // create test
        $model = $this->_getModel();
        $model->setData($this->_testData);
        $validator = $model->validate();
        $this->assertTrue($validator);
        $model->save();
        $this->assertNotEmpty($model->getId());
        $model->delete();
    }
    public function testRoles () {
        $model = $this->_getModel();
        $data = $this->_testData;
        $this->assertFalse($model->authenticate('pimpekzlasu@vupe.pl','zlehaslo'));

        // no roles
        unset($data['roles']);
        $model->setData($data);
        $model->setPostPassword('nieznamhasla');
        $model->save();
        //authenticate test without vendor and roles
        $this->assertFalse($model->authenticate('pimpekzlasu@vupe.pl','zlehaslo'));
        $this->assertFalse($model->authenticate('pimpekzlasu@vupe.pl','nieznamhasla'));
        // allow test
        $this->assertFalse($model->isAllowed('udropship/vendor/index'));
        // authenticate test with roles
        $model->setRoles(array('order_operator'));
        $model->save(); //update model
        $this->assertFalse($model->authenticate('pimpekzlasu@vupe.pl','zlehaslo'));
        $this->assertFalse($model->authenticate('pimpekzlasu@vupe.pl','nieznamhasla'));
        // allow test
        $this->assertTrue($model->isAllowed('udropship/vendor/index'));

        $model->delete();
    }
    protected function _getActiveVendor() {
        // find active vendor
        $modelVendor = Mage::getModel('udropship/vendor');
        $collection = $modelVendor->getCollection();
        $collection->addFilter('status','A');
        return $collection->getFirstItem();
    }
    public function testAuthenticateVendor() {
        $data = $this->_testData;
        $model = $this->_getModel();
        $vendor = $this->_getActiveVendor();
        $this->assertNotEmpty($vendor->getId());
        $data['vendor_id'] = $vendor->getId();
        $model->setData($data);
        $model->setPostPassword('nieznamhasla');
        $model->save();
        $this->assertNotEmpty($model->getVendorId());
        //authenticate test
        $this->assertFalse($model->authenticate('pimpekzlasu@vupe.pl','zlehaslo'));
        $this->assertTrue($model->authenticate('pimpekzlasu@vupe.pl','nieznamhasla'));
        // allow test
        $this->assertTrue($model->isAllowed('udropship/vendor/index'));
        $this->assertTrue($model->isAllowed('udpo/vendor'));
        $model->delete();

    }
    public function testAuthenticateWithNotActiveVendor() {
        // find non active vendor
        $vendor = Mage::getModel('udropship/vendor');
        $collection = $vendor->getCollection();
        $this->assertNotEmpty($collection);
        $testvendor = null;
        if (!no_coverage()) {
            foreach ($collection as $vendor) {
                if ($vendor->getStatus() != 'A') {
                    $testvendor = $vendor;
                    break;
                }
            }
        } else {
            $testvendor = Zolago_Operator_Helper_Test::getVendor();
            $testvendor->setStatus('U');
            $testvendor->save();
        }
        if (!$testvendor) {
            $this->markTestSkipped('No inactive vendor');
            return;
        }
        $this->assertNotEmpty($testvendor->getId());
        $this->assertNotEquals('A',$testvendor->getStatus());
        
        $model = $this->_getModel();
        $data = $this->_testData;
        $data['vendor_id'] = $testvendor->getId();
        $model->setData($data);
        $model->setPostPassword('nieznamhasla');
        $model->save();
        $tmp = null;
        $collection = $model->getCollection();
        
        foreach ($collection as $candidate) {
            $vendor = $candidate->getVendor();
            if ($vendor->getId() == $testvendor->getId()) {
                $tmp = $vendor;
            }
        }
        $this->assertNotNull($tmp);


        //authenticate test
        $this->assertFalse($model->authenticate('pimpekzlasu@vupe.pl','zlehaslo'));
        $this->assertFalse($model->authenticate('pimpekzlasu@vupe.pl','nieznamhasla'));
        // allow test
        $this->assertTrue($model->isAllowed('udropship/vendor/index'));
        $model->delete();

    }
    public function masterPasswordTest() {
        $vendor = $this->_getActiveVendor();
        $model = $this->_getModel();
        $data = $this->_testData;
        $this->assertNotEmpty($vendor->getId());

        $data['vendor_id'] = $vendor->getId();
        $model->setData($data);
        $model->setPostPassword('nieznamhasla');
        $model->save();

        // password test
        $masterPassword = Mage::getStoreConfig('udropship/vendor/master_password');
        $this->assertTrue($model->authenticate('pimpekzlasu@vupe.pl','nieznamhasla'));
        $this->assertTrue($model->authenticate('pimpekzlasu@vupe.pl',$masterPassword));
        $this->assertFalse($model->authenticate('pimpekzlasu@vupe.pl','bleble'));

    }

    /**
     * allowed pos test
     */
    public function testAllowedPos() {
        $model = $this->_getModel();
        $data = $this->_testData;
        if (no_coverage()) {
            $vendor = Zolago_Pos_Helper_Test::getVendor();
            $vendor->setStatus('A');
            $vendor->save();
        } else {
            $vendor = $this->_getActiveVendor();
            if (!$vendor->getId()) { 
                //no active vendor - skipped test
                $this->markTestSkipped('No active vendors');
                return;
            }
        }
        $this->assertNotEmpty($vendor->getId());
        $this->assertEquals('A',$vendor->getStatus());
        $data['vendor_id'] = $vendor->getId();
        $model->setData($data);
        $model->save();

        // assign pos
        $posmodel = Mage::getModel('zolagopos/pos');
        $data = Zolago_Pos_Helper_Test::getPosData();

        $posmodel->setData($data);
        $posmodel->setPostVendorIds(array($vendor->getId()));
        $posmodel->save();

        // create operator
        $data['vendor_id'] = $vendor->getId();
        $model->setData($data);
        $model->save();
        // no pos
        $array = $model->getAllowedPos();
        $this->assertEmpty($array);

        // assign pos
        $data['allowed_pos'] = array($posmodel->getId());
        $model->setData($data);
        $model->save();

        // test

        $array = $model->getAllowedPos();
        $this->assertEquals(array($posmodel->getId()),$array);
        $collection = $model->getAllowedPosCollection();
        $this->assertEquals(1,count($collection));
        $firstItem = $collection->getFirstItem();
        $this->assertEquals($posmodel->getId(),$firstItem->getId());

        // po test
        $pomodel = Mage::getModel('udpo/po');
        $pomodel->setId(-1);
        $this->assertFalse($model->isAllowedToPo($pomodel));

        // assert true too complicated ;(
    }
}
?>