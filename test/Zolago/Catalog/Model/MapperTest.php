<?php
class Zolago_Catalog_Model_MapperTest extends ZolagoDb_TestCase
{
    protected $_model;
    protected $mapperClass;

    protected function _getModel()
    {
        if (empty($this->_model)) {
            $this->_model = Mage::getModel('zolagocatalog/mapper');
            $this->assertNotEmpty($this->_model);
        }
        return $this->_model;
    }


    public function testCreate()
    {
        $this->_getModel();
    }

    public function setUp()
    {
        $this->mapperClass = Mage::getModel('zolagocatalog/mapper');
    }

    public function testMapperClass()
    {
        $this->assertInstanceOf('Zolago_Catalog_Model_Mapper', $this->mapperClass);
    }


    public function testGetTargetNameMethod()
    {
        $r = new ReflectionClass('Zolago_Catalog_Model_Mapper');
        $m = $r->getMethod('_getTargetName');
        $m->setAccessible(true);
        $result = $m->invoke($this->mapperClass, 'banner');

        $this->assertNotEmpty($result);
    }


    public function testSaveImageMethod()
    {
        $r = new ReflectionClass('Zolago_Catalog_Model_Mapper');
        $m = $r->getMethod('_saveImage');
        $m->setAccessible(true);
        $result = $m->invoke($this->mapperClass);

        $this->assertFalse($result);
    }

}