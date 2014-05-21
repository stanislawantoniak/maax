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
        $imgfile = 'test.jpg';
        $target = 'test';

        $result = $m->invoke($this->mapperClass, $imgfile, $target);

        $this->assertNotNull($result);
    }

    public function testCollection()
    {
        $model = $this->_getModel();
        $collection = $model->getCollection();

        //Collection shouldn't be empty
        $this->assertGreaterThan(0,count($collection));
    }


    public function testGetImageId()
    {
        $r = new ReflectionClass('Zolago_Catalog_Model_Mapper');
        $m = $r->getMethod('_getImageId');
        $m->setAccessible(true);

        $result = $m->invoke($this->mapperClass, 1,2,3);
        $this->assertNotNull($result);
    }

    public function testAddImageToGallery()
    {
        $r = new ReflectionClass('Zolago_Catalog_Model_Mapper');
        $m = $r->getMethod('_addImageToGallery');
        $m->setAccessible(true);


        $result = $m->invoke($this->mapperClass, 1, 0, '', '');
        $this->assertNotNull($result);
    }

}