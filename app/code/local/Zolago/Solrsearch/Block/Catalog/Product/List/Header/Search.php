<?php
/**
 * Description of Title
 */
class Zolago_Solrsearch_Block_Catalog_Product_List_Header_Search
	extends Zolago_Solrsearch_Block_Catalog_Product_List_Header_Abstract {
	
	protected function _construct(){
		$this->setTemplate('zolagosolrsearch/catalog/product/list/header/search.phtml');
	}
}