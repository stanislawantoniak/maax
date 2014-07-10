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
		
		// Create product list 
		$products = array();
		foreach ($listModel->getCollection() as $product){
			/* @var $_product Zolago_Solrsearch_Model_Catalog_Product */
			$_product = $product->getData();
			$_product['listing_resized_image_url'] = $product->getListingResizedImageUrl();
			$products[] = $_product;
		}
		
		// Header
		$type = $listModel->getMode()==$listModel::MODE_SEARCH ? "search" : "category";
		$header = $this->getLayout()->createBlock("zolagosolrsearch/catalog_product_list_header_$type")->toHtml();
		
		// Toolbar
		$toolbar = $this->getLayout()->createBlock("zolagosolrsearch/catalog_product_list_toolbar")->toHtml();
		
		// Filter
		$filters = $this->getLayout()->createBlock("zolagosolrsearch/catalog_product_list_faces")->toHtml();
		
		$content=array(
			"products" => $products,
			"header" => $header,
			"toolbar" => $toolbar,
			"filters" => $filters
		);
		
		$result = $this->_formatSuccessContentForResponse($content);
				
		$this->getResponse()
                ->clearHeaders()
                ->setHeader('Content-type', 'application/json')
                ->setBody(Mage::helper('core')->jsonEncode($result));
	}
    
}