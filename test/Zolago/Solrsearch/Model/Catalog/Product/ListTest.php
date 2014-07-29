<?php
class Zolago_Solrsearch_Model_Catalog_Product_ListTest extends ZolagoDb_TestCase {
	
    public function testCreate() {
        $obj = Mage::getModel('zolagosolrsearch/catalog_product_list');        
        $this->assertNotNull($obj);
        $this->assertInstanceOf('Zolago_Solrsearch_Model_Catalog_Product_List',$obj);
        $this->assertNotNull($obj->getMode());
        $this->assertNotNull($obj->getCurrentCategory());
        
    }	
    public function testCollection() {
        $obj = Mage::getModel('zolagosolrsearch/catalog_product_list');
        $data = $obj->getSolrData();
        $this->assertNotNull($data);
        $this->assertNotNull($obj->getCurrentPage());
        $this->assertNotNull($obj->getDefaultDir());
        $this->assertNotNull($obj->getDefaultOrder());
        $this->assertNotNull($obj->getCurrentOrder());
        $this->assertNotNull($obj->getCurrentDir());
        $this->assertNotNull($obj->getSortOptions());
//        $collection = $obj->getCollection();
//        echo get_class($collection);
//        $this->assertInstanceOf('adfa',$collection);
    }
    
}