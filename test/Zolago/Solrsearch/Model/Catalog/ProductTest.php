<?php
class Zolago_Solrsearch_Model_Catalog_ProductTest extends ZolagoDb_TestCase {
	
    public function testCreate() {
        $obj = Mage::getModel('zolagosolrsearch/catalog_product');        
        $this->assertNotNull($obj);
        $this->assertInstanceOf('Zolago_Solrsearch_Model_Catalog_Product',$obj);
    }	
    
}