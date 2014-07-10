<?php

class Orba_Common_Ajax_ListingController extends Orba_Common_Controller_Ajax {
	/**
	 * Init category an register it
	 */
    protected function _initCategory() {
		$categoryId = $this->getRequest()->getParam("scat", 0);
		$catModel = null;
		if($categoryId){
			$catModel = Mage::getModel("catalog/category")->load($categoryId);
		}
		if(!$catModel || !$catModel->getId()){
			$catModel = Mage::helper("zolagodropshipmicrosite")->getVendorRootCategoryObject();
		}
		Mage::register("current_category", $catModel);
	}
	
	public function get_blocksAction() {
		$this->_initCategory();
		$listModel = Mage::getSingleton("zolagosolrsearch/catalog_product_list");
		/* @var $listModel Zolago_Solrsearch_Model_Catalog_Product_List */
		
		//$filters = $this->getLayout()->createBlock("zolagosearch/");
		$products = array();
		foreach ($listModel->getCollection() as $product){
			$_product = $product->getData();
			$products[] = $_product;
		}
		$content=array(
			"products" => $products
		);
		
		$result = $this->_formatSuccessContentForResponse($content);
				
		$this->getResponse()
                ->clearHeaders()
                ->setHeader('Content-type', 'application/json')
                ->setBody(Mage::helper('core')->jsonEncode($result));
	}
    
}