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
    public function testAuthenticateVendor() {
        $model = $this->_getModel();
        $data = $this->_testData;

        $vendor = Zolago_Operator_Helper_Test::getVendor();
        $vendor->setStatus('A');
        $vendor->save();
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
        $vendor = Zolago_Operator_Helper_Test::getVendor();
        $model = $this->_getModel();
        $vendor->setStatus('O');
        $vendor->save();
        $data = $this->_testData;
        $this->assertNotEmpty($vendor->getId());
        $data['vendor_id'] = $vendor->getId();
        $model->setData($data);
        $model->setPostPassword('nieznamhasla');        
        $model->save();        

        //authenticate test 
        $this->assertFalse($model->authenticate('pimpekzlasu@vupe.pl','zlehaslo'));
        $this->assertFalse($model->authenticate('pimpekzlasu@vupe.pl','nieznamhasla'));
        // allow test
        $this->assertTrue($model->isAllowed('udropship/vendor/index'));
        $model->delete();

    }
    public function masterPasswordTest() {
        $vendor = Zolago_Operator_Helper_Test::getVendor();
        $model = Mage::getModel('zolagooperator/operator');
        $vendor->setStatus('A');
        $vendor->save();
        $data = $this->_testData;
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


}
?>