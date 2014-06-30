<?php
class Zolago_Solrsearch_Model_Observer {
	
	/**
	 * @var Mage_Catalog_Model_Product
	 */
	protected $_tmpProduct;
	
	/**
	 * @var array
	 */
	protected $_collectedProdutcs = array();


	/**
	 * Add product to queue. If scope is default - reindex all cores
	 * @param Mage_Core_Model_Observer $observer
	 * @return type
	 */
	public function catalogProductSaveAfter(Varien_Event_Observer $observer) {
		$product = $observer->getEvent()->getProduct();
		if(!($product instanceof Mage_Catalog_Model_Product)){
			return;
		}
		$this->_pushProduct($product, $product->getStoreId());
		
	}
	
	/**
	 * Collect affected products ids
	 * @param Varien_Event_Observer $observer
	 */
	public function catalogruleApplyAfter(Varien_Event_Observer $observer)
	{
		$productCondition = $observer->getEvent()->getData('product_condition');
		$adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
		$productCondition = $productCondition->getIdsSelect($adapter)->__toString();
		$effectedProducts = $adapter->fetchAll($productCondition);
		foreach ($effectedProducts as $item)
		{
			if (isset($item['product_id']) && $item['product_id'] > 0) {
				$this->_collectedProdutcs[] = $item['product_id'];
			}
		}
	}
	
	public function afterReindexProcessCatalogProductPrice(
			Varien_Event_Observer $observer) {
		
	}
	
	
	
	protected function _deleteSolrDocument() {
		
	}
	
	
	/**
	 * Push single product to queue. Process parent if needed
	 * @param Mage_Catalog_Model_Product|int $product
	 * @param type $storeId
	 * @return type
	 */
	protected function _pushProduct($product, $storeId=Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
		
		if(!($product instanceof Mage_Catalog_Model_Product)){
			$product = Mage::getModel("catalog/product")->setStoreId($storeId)->load($product);
		}
		
		// Product is simple and not visiable indyvidually - preindex its parent
		// Do recursion
		if($product->getTypeId()==Mage_Catalog_Model_Product_Type::TYPE_SIMPLE ){
			
			$parentProducts = Mage::getResourceSingleton('catalog/product_type_configurable')
				->getParentIdsByChild($product->getId());
			
			foreach($parentProducts as $parentProductId){
				$this->_pushProduct($parentProductId, $storeId);
			}
			// Product not visible - just skip 
			// Each way - reindex proeuct
			if($product->getVisibility()==Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE){
				return;
			}
		}
		
		$queueModel = Mage::getSingleton('zolagosolrsearch/queue');
		/* @var $queueModel Zolago_Solrsearch_Model_Queue */
				

		if(empty($storeId) || $storeId==Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID){
			$storeId = $product->getStoreIds();
		}else{
			$storeId = array($storeId);
		}
		
		foreach($storeId as $_storeId){
			$cores = Mage::helper("zolagosolrsearch")->getCoresByStoreId($_storeId);
			if(!is_array($cores) || !isset($cores[0])){
				continue;
			}

			$item = Mage::getModel("zolagosolrsearch/queue_item");
			/* @var $item Zolago_Solrsearch_Model_Queue_Item */

			$item->setProductId($product->getId());
			$item->setCoreName($cores[0]);
			$item->setStoreId($_storeId);
			$queueModel->push($item);
		}

	}
	
	/**
	 * @return Mage_Catalog_Model_Product
	 */
	protected function getTmpProduct() {
		if(!$this->_tmpProduct){
			$this->_tmpProduct = Mage::getModel("catalog/product");
		}
		return $this->_tmpProduct;
	}
	
	public function doNothing(Varien_Event_Observer $observer) {
		Mage::log("Nothing");
	}
}

?>
