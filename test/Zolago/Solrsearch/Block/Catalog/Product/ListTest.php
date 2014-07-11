<?php
class Zolago_Solrsearch_Block_Catalog_Product_ListTest extends ZolagoDb_TestCase {
	
    public function testCreate() {
        $obj = Mage::app()->getLayout()->createBlock('zolagosolrsearch/catalog_product_list');
        $this->assertNotEmpty($obj);        
        $this->assertInstanceOf('Zolago_Solrsearch_Block_Catalog_Product_List',$obj);
    }	
    
}