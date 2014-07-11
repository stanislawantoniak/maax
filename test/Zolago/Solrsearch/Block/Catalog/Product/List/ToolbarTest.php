<?php
class Zolago_Solrsearch_Block_Catalog_Product_List_ToolbarTest extends ZolagoDb_TestCase {
	
    public function testCreate() {
        $obj = Mage::app()->getLayout()->createBlock('zolagosolrsearch/catalog_product_list_toolbar');
        $this->assertNotEmpty($obj);        
        $this->assertInstanceOf('Zolago_Solrsearch_Block_Catalog_Product_List_Toolbar',$obj);
    }	
    
}