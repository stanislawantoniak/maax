<?php
/**
 * Description of Title
 */
class Zolago_Solrsearch_Block_Catalog_Product_List_Header_Search
	extends Zolago_Solrsearch_Block_Catalog_Product_List_Header_Abstract {
	
	protected function _construct(){
		$this->setTemplate('zolagosolrsearch/catalog/product/list/header/search.phtml');
	}

    public function getCurrentSearchHtml() {
        if (!$this->getData('current-search')) {
            $html = $this->getLayout()
                ->createBlock('core/template')
                ->setBlockId('current-search')
                ->setTemplate('zolagosolrsearch/catalog/product/list/header/current-search.phtml')
                ->toHtml();
            $this->setData('current-search', $html);
        }
        return $this->getData('current-search');
    }

}