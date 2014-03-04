<?php
class Zolago_Mapper_Model_Observer {
	public function zolagoMapperSaveAfter($observer) {
		$event = $observer->getEvent();
		$object = $event->getDataObject();
		$id = $object->getId();
		$queue = Mage::getModel('zolagomapper/queue_mapper');
		$queue->push($id);
	}
	public function catalogProductSaveAfter($observer) {
		$id = $observer->getEvent()
						   ->getDataObject()
						   ->getId();
		$queue = Mage::getModel('zolagomapper/queue_product');
		$elem = array ( 
			'product_id' => $id,
			'website_id' => Mage::app()->getStore(true)->getWebsite()->getId(),
		);
		$queue->push($elem);
	}
	static public function processMaperQueue() {
		$model = Mage::getModel('zolagomapper/queue_mapper')->process();		
	}
	static public function processProductQueue() {
		$model = Mage::getModel('zolagomapper/queue_product')->process();
		
	}
}