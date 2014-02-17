<?php
class Zolago_Pos_Model_PosTest extends Zolago_TestCase {
    
    
    protected $_model;
    
    protected function _getModel() {
        if (empty($this->_model)) {
            $this->_model = Mage::getModel('zolagopos/pos');
        }
        return $this->_model;
    }
    /**
     * new object test
     */
     public function testCreate() {
         $obj = $this->_getModel();
         $this->assertEquals('Zolago_Pos_Model_Pos',get_class($obj));
     }
     
    /**
     * region test
     */
    
    public function testRegion() {
        $transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
        $transaction->beginTransaction();
        
        $obj = $this->_getModel();
        $this->assertEmpty($obj->getRegionText());
        $data = Zolago_Pos_Helper_Test::getPosData();
        $obj->setData($data);
        $obj->save();        
        $this->assertNotEmpty($obj->getRegionText());

        $transaction->rollback();
    }
    
    
    /**
     * validator test     
     */
    protected function _validateTest($testKey,$testField,$expected) {
        $model = $this->_getModel();
        $posData = Zolago_Pos_Helper_Test::getPosData();
        $testData = $posData;
        $testData[$testKey] = $testField;
        $model->setData($testData);
        $validator = $model->validate();
        $this->assertContains($expected,$validator);
    }
    /**
     * vendor collection test
     */
    public function testPosFunctions() {
        // transaction
        $transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
        $transaction->beginTransaction();
                               
        // too long client_number
        $this->_validateTest('client_number',str_repeat('ab',100),Mage::helper('zolagopos')->__('Max length of Client number is 100'));        
        //  empty minimal_stock
        $this->_validateTest('minimal_stock',null,Mage::helper('zolagopos')->__('Minimal stock is required'));        
        $this->_validateTest('minimal_stock',null,Mage::helper('zolagopos')->__('Minimal stock is not number'));        
        // too long external_id
        $this->_validateTest('external_id',str_repeat('ab',100),Mage::helper('zolagopos')->__('Max length of External id is 100'));        
        // no is_active
        $this->_validateTest('is_active',null,Mage::helper('zolagopos')->__('Is active is required'));        
        // empty name        
        $this->_validateTest('name',null,Mage::helper('zolagopos')->__('Name is required'));        
        // too long name
        $this->_validateTest('name',str_repeat('ab',100),Mage::helper('zolagopos')->__('Max length of Name is 100'));        
        // too long company   
        $this->_validateTest('company',str_repeat('ab',100),Mage::helper('zolagopos')->__('Max length of Company is 150'));        
        // empty country_id
        $this->_validateTest('country_id',null,Mage::helper('zolagopos')->__('Country is required'));        
        // wrong region_id
        $this->_validateTest('region_id','baddas',Mage::helper('zolagopos')->__('Region is not number'));        
        // too long region
        $this->_validateTest('region',str_repeat('ab',100),Mage::helper('zolagopos')->__('Max length of Region is 100'));                
        // empty postcode
        $this->_validateTest('postcode',null,Mage::helper('zolagopos')->__('Postcode is required'));        
        // wrong postcode
        $this->_validateTest('postcode','334-43',Mage::helper('zolagopos')->__('Postcode has not valid format (ex.12-345)'));        
        // empty street
        $this->_validateTest('street',null,Mage::helper('zolagopos')->__('Street is required'));        
        // too long street
        $this->_validateTest('street',str_repeat('ab',100),Mage::helper('zolagopos')->__('Max length of Street is 150'));        
        // empty city
        $this->_validateTest('city',null,Mage::helper('zolagopos')->__('City is required'));        
        // too long city
        $this->_validateTest('city',str_repeat('ab',100),Mage::helper('zolagopos')->__('Max length of City is 100'));        
        // too long email
        $this->_validateTest('email',str_repeat('ab',100),Mage::helper('zolagopos')->__('Max length of Email is 100'));        
        // empty phone
        $this->_validateTest('phone',null,Mage::helper('zolagopos')->__('Phone is required'));        
        // too long phone
        $this->_validateTest('phone',str_repeat('ab',100),Mage::helper('zolagopos')->__('Max length of Phone is 50'));        
        
        $transaction->rollback();
    }
    
    
    /**
     * test save function 
     */
     public function testSave() {
        $transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
        $transaction->beginTransaction();
        
        $model = $this->_getModel();
        $posData = Zolago_Pos_Helper_Test::getPosData();
        $posData['is_active'] = 0;
        
        $model->setData($posData);
        $validator = $model->validate();
        $this->assertTrue($validator);
        $model->save();
        $this->assertNotEmpty($model->getId());
        // vendor collection
        $collection = $model->getVendorCollection();
        $this->assertEquals(0,count($collection));
        
        $vendor = Zolago_Pos_Helper_Test::getVendor();
        $id = $vendor->getId();
        $model->setPostVendorIds(array($id));
        $model->save();
        
        $collection = $model->getVendorCollection();
        $this->assertEquals(1,count($collection));
        
        $transaction->rollback();       
     }
         
}
?>