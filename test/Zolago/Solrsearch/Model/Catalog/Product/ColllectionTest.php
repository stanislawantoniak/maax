<?php
class Zolago_Solrsearch_Model_Catalog_Product_CollectionTest extends ZolagoDb_TestCase {
	
    public function testCreate() {
        $obj = Mage::getModel('zolagosolrsearch/catalog_product_collection');        
        $this->assertNotNull($obj);
        $this->assertInstanceOf('Zolago_Solrsearch_Model_Catalog_Product_Collection',$obj);
        
        
    }	
    
}