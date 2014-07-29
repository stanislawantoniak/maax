<?php
class Zolago_Catalog_Vendor_PriceController extends Zolago_Dropship_Controller_Vendor_Abstract
{
	
	public function indexAction() {
		$this->_renderPage(null, 'udprod_price');
	}
	
	public function restAction() {
		
		$collection = Mage::getResourceModel("catalog/product_collection");
		/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
		$collection->addAttributeToFilter("udropship_vendor", $this->getVendor()->getId());
		$collection->addAttributeToFilter("type_id", "configurable");
		$collection->addAttributeToSelect("name", "left");
		
		$total = $collection->getSize();
		
		// Prepare sort
		
		// Pepare range
		$range = $this->getRequest()->getHeader("Range");
		if(!$range){
			$range = $this->getRequest()->getHeader("X-Range");
		}
		
		if($range){
			preg_match('/(\d+)-(\d+)/', $range, $matches);

			$start = $matches[1];
			$end = $matches[2];
			if($end > $total){
				$end = $total;
			}
		}else{
			$start = 0;
			$end = 40;
		}
		
		// Make limit
		$collection->getSelect()->limit($end-$start, $start);
		
		
		$out = array();
		
		foreach($collection as $product){
			$product->setCollapsed(false);
			$out[] = $product->getData();
		}
		
		$this->getResponse()->
				setHeader('Content-type', 'application/json')->
				setHeader('Content-Range', 'items ' . $start. '-' . $end. '/' . $total)->
				setBody(Mage::helper("core")->jsonEncode($out));
		
	}
	
		/**
	 * @return Unirgy_Dropship_Model_Vendor
	 */
	public function getVendor() {
		return $this->_getSession()->getVendor();
	}

}



