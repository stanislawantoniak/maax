<?php
class Zolago_Mapper_Model_Observer {
	public function zolagoMapperSaveAfter($observer) {
		$event = $observer->getEvent();
		$object = $event->getDataObject();
		$id = $object->getId();
		$queue = Mage::getModel('zolagomapper/queue_mapper');
		$queue->push($id);
	}
	public function zolagoMapperDeleteBefore($observer) {
		$event = $observer->getEvent();
		$object = $event->getDataObject();
		if($object->getId()){
			$productIds = Mage::getResourceModel("zolagomapper/index")
					->getProductIdsByMapper($object);
			$object->setData("affected_products_ids", $productIds);
		}
	}
	public function zolagoMapperDeleteAfter($observer) {
		$event = $observer->getEvent();
		$object = $event->getDataObject();
		if($object->getData("affected_products_ids")){
			Mage::getResourceModel("zolagomapper/index")->assignWithCatalog(
				$object->getData("affected_products_ids")
			);
		}
	}
	public function catalogProductSaveAfter($observer) {
		$product = $observer->getEvent()->getDataObject();
		if($product instanceof Mage_Catalog_Model_Product){
			if(!$this->_checkMappedAttributeChanged($product)){
				return;
			}
			$id = $product->getId();
			$queue = Mage::getModel('zolagomapper/queue_product');
			$elem = array ( 
				'product_id' => $id,
				'website_id' => Mage::app()->getStore($observer->getEvent()->getDataObject()->getStoreId())->getWebsiteId()
			);
			$queue->push($elem);
		}
	}
	
	public function catalogProductAttributeUpdateAfter($observer) {
		$event = $observer->getEvent();
		$attrData = $event->getAttributesData();
		$productIds = $event->getProductIds();
		$storeId = $event->getStoreId();
		// arrtCode => value, ...
		if(!is_array($productIds) || empty($productIds)){
			return;
		}
		if(!is_array($attrData) || empty($attrData)){
			return;
		}
		
		if($this->_isAnyCodeMappable(array_keys($attrData))){
			$queue = Mage::getModel('zolagomapper/queue_product');
			foreach($productIds as $id){
				$elem = array ( 
					'product_id' => $id,
					'website_id' => Mage::app()->getStore($storeId)->getWebsiteId()
				);
				$queue->push($elem);
			}
		}
	}
	
	static public function processMaperQueue() {
		$model = Mage::getModel('zolagomapper/queue_mapper')->process();		
	}
	
	static public function processProductQueue() {
		$model = Mage::getModel('zolagomapper/queue_product')->process();
		
	}
	
	protected function _isAnyCodeMappable(array $codes) {
		$attrColl = Mage::getResourceModel("catalog/product_attribute_collection");
		/* @var $attrColl Mage_Catalog_Model_Resource_Product_Attribute_Collection */
		$attrColl->addFieldToFilter("attribute_code", array("in"=>$codes));
		$attrColl->addFieldToFilter("is_mappable", 1);
		return (bool)$attrColl->count();
	}
	
	protected function _checkMappedAttributeChanged(Mage_Catalog_Model_Product $product) {
		foreach ($product->getAttributes() as $attribute){
			if($attribute->getIsMappable()){
				$code = $attribute->getAttributeCode();
				if($product->getOrigData($code) != $product->getData($code)){
					return true;
				}
			}
		}
		return false;
	}
}