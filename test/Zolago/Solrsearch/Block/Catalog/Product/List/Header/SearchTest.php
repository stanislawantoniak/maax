<?php
class Zolago_Solrsearch_Block_Catalog_Product_List_Header_SearchTest extends ZolagoDb_TestCase {
	
    public function testCreate() {
        $obj = Mage::app()->getLayout()->createBlock('zolagosolrsearch/catalog_product_list_header_search');
        $this->assertNotEmpty($obj);        
        $this->assertInstanceOf('Zolago_Solrsearch_Block_Catalog_Product_List_Header_Search',$obj);
    }	
    
}