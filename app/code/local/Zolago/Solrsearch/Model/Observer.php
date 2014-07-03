<?php
class Zolago_Solrsearch_Model_Observer {
	
	/**
	 * @var Mage_Catalog_Model_Product
	 */
	protected $_tmpProduct;
	
	/**
	 * Prodcut by sote to index
	 * @var array
	 */
	protected $_collectedProdutcs = array();
	
	/**
	 * Parents check
	 * @var type 
	 */
	protected $_collectedCheckParents = array();
	
	
	/**
	 * Are colleced product handled?
	 * @var bool
	 */
	protected $_handled = false;

	
	/**
	 * Add product to queue.
	 * @param Mage_Core_Model_Observer $observer
	 * @return type
	 */
	public function catalogProductDeleteBefore(Varien_Event_Observer $observer) {
		$product = $observer->getEvent()->getProduct();
		if(!($product instanceof Mage_Catalog_Model_Product)){
			return;
		}
		$this->_pushProduct($product, $product->getStoreId(), true, true);
		
	}
	
	
	/**
	 * Add product to queue.
	 * @param Mage_Core_Model_Observer $observer
	 * @return type
	 */
	public function catalogProductSaveAfter(Varien_Event_Observer $observer) {
		$product = $observer->getEvent()->getProduct();
		if(!($product instanceof Mage_Catalog_Model_Product)){
			return;
		}

		/**
		 * @todo add check solr-used attribute changed?
		 */
		
		$this->_pushProduct($product, $product->getStoreId(), true);
		
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
		$availableStores = $this->_filterStoreIds(array_keys(Mage::app()->getStores()));
		
		foreach ($effectedProducts as $item)
		{
			if (isset($item['product_id']) && $item['product_id'] > 0) {
				foreach($availableStores as $storeId){
					$this->collectProduct($item['product_id'], $storeId);
				}
			}
		}
	}
	
	
	/**
	 * Collect produc of category save
	 * @param Varien_Event_Observer $observer
	 */
	public function catalogCategorySaveAfter(Varien_Event_Observer $observer) {
		$category = $observer->getEvent()->getCategory();
		/* @var $category Mage_Catalog_Model_Category */
		
		////////////////////////////////////////////////////////////////////////
		// Did product changed on depends attributes chnaged?
		////////////////////////////////////////////////////////////////////////
		$shouldProcess = false;
		
		$affectedIds = $category->getAffectedProductIds();
		if(!$affectedIds){
			$affectedIds = array();
		}else{
			$shouldProcess = true;
		}
		
		foreach($this->_getChangableCategoryAttributes() as $attrCode){
			if($category->getData($attrCode)!=$category->getOrigData($attrCode)){
				$shouldProcess = true;
				break;
			}
		}
		
		if(!$shouldProcess){
			return;
		}
		
		////////////////////////////////////////////////////////////////////////
		// Process scopes
		////////////////////////////////////////////////////////////////////////		
		$storeId = $category->getStoreId();
		$storeIds=array();
		
		if($storeId==Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID){
			$storeIds = $category->getStoreIds();
		}else{
			$storeIds = array($storeId);
		}
		
		foreach($this->_filterStoreIds($storeIds) as $storeId){
			$regualrIds = $category->
				setStoreId($storeId)->
				getProductCollection()->
					// Filter only product visible and enabled in current category in scope
					addAttributeToFilter("status", Mage_Catalog_Model_Product_Status::STATUS_ENABLED)->
					addAttributeToFilter("visibility",
						array("neq"=>Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))->
					getAllIds();
			
			if(!$regualrIds){
				$regualrIds = array();
			}
			
			$productsIds = array_unique($affectedIds + $regualrIds);
		
			foreach($productsIds as $productId){
				$this->collectProduct($productId, $category->getStoreId());
			}
		}
	}
	
	
	/**
	 * @param Varien_Event_Observer $observer
	 */
	public function catalogProductAttributeUpdateAfter(
			Varien_Event_Observer $observer) {
		
		$event = $observer->getEvent();
		$productIds = $event->getProductIds();
		$storeId = $event->getStoreId();
		
		/**
		 * @todo add check solr-used attribute changed?
		 */
		
		if($storeId==Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID){
			$storeIds = array_keys(Mage::app()->getStores());
		}else{
			$storeIds = array($storeId);
		}
		
		foreach($this->_filterStoreIds($storeIds) as $storeId){
			foreach($productIds as $productId){
				$this->collectProduct($productId, $storeId, true);
			}
		}
	}
	
	
	/**
	 * After mapper assign products
	 * @param Varien_Event_Observer $observer
	 */
	public function zolagoMapperAfterAssignProducts(
			Varien_Event_Observer $observer) {
		
		
		$event = $observer->getEvent();
		$productIds = $event->getProductIds();
	
		$storeIds = array_keys(Mage::app()->getStores());
		
		foreach($this->_filterStoreIds($storeIds) as $storeId){
			foreach($productIds as $productId){
				$this->collectProduct($productId, $storeId);
			}
		}
		
	}
	
	
	/**
	 * @param Varien_Event_Observer $observer
	 */
	public function afterReindexProcessCatalogProductPrice(
			Varien_Event_Observer $observer) {
		
	}
	
	/**
	 * Process after response send - if has some collected products process it
	 * @param Varien_Event_Observer $observer
	 */
	public function controllerFrontSendResponseAfter(
			Varien_Event_Observer $observer=null) {
		if($this->_collectedProdutcs){
			$this->processCollectedProducts();
		}
		$this->_handled = true;
	}
	
	
	/**
	 * @param int|Mage_Catalog_Model_Product $product
	 * @param type $storeId
	 */
	public function collectProduct($product, $storeId=Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID, $checkParents=false) {
		
		if($product instanceof Mage_Catalog_Model_Product){
			$productId = $product->getId();
		}else{
			$productId = $product;
		}
		
		if(!isset($this->_collectedProdutcs[$storeId])){
			$this->_collectedProdutcs[$storeId] = array();
		}
		
		if(!isset($this->_collectedCheckParents[$storeId])){
			$this->_collectedCheckParents[$storeId] = array();
		}
		
		if($productId){
			$this->_collectedProdutcs[$storeId][$productId] = $product;
			$this->_collectedCheckParents[$storeId][$productId] = $checkParents;
		}
		
		
	}
	
	
	/**
	 * Process collected products
	 */
	public function processCollectedProducts() {
		
		$resource = Mage::getResourceModel("zolagosolrsearch/improve");;
		/* @var $resource Zolago_Solrsearch_Model_Resource_Improve */
	
		
		foreach($this->_collectedProdutcs as $storeId=>$products){
			
			$stores = array();
			$childsIds = array();
			$parentIdsFlat = array();
			
			foreach($products as $productId){
				if(isset($this->_collectedCheckParents[$storeId][$productId])){
					$childsIds[] = $productId;
				}
			}
			
			if($childsIds){
				$parentIds = $resource->getParentIdsByChild($childsIds);
				foreach($parentIds as $parentIds){
					$parentIdsFlat = array_merge($parentIdsFlat, $parentIds);
				}
			}
			
			$collection = Mage::getResourceModel('catalog/product_collection');
			/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
			
			$collection->addAttributeToSelect(array("status", "visibility"));
			
			if($storeId!=Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID){
				$collection->addStoreFilter($storeId);
				$collection->setStoreId($storeId);
			}else{
				$stores = $resource->getStoreIdsByProducts($products);
			}
			
			$collection->addIdFilter($products + $parentIdsFlat);
			
			
			foreach($collection as $product){
				if(isset($stores[$product->getId()])){
					$product->setStoreIds($stores[$product->getId()]);
				}
				$this->_pushProduct($product, $storeId);
			}
			
		}
		
	}
	
	/**
	 * Push single product to queue. Process parent if needed
	 * @param Mage_Catalog_Model_Product|int $product
	 * @param type $storeId
	 * @param bool $deleteOnly
	 * @return type
	 */
	protected function _pushProduct($product, 
			$storeId=Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID, 
			$checkParents = false, $deleteOnly=false) {

		if(!$product instanceof Mage_Catalog_Model_Product){
			$product = Mage::getModel("catalog/product")->setStoreId($storeId)->load($product);
		}
		
		// Product is simple and not visiable indyvidually - preindex its parent
		// Do recursion
		if($checkParents && $product->getTypeId()==Mage_Catalog_Model_Product_Type::TYPE_SIMPLE){
			$parentProducts = Mage::getResourceSingleton('catalog/product_type_configurable')
				->getParentIdsByChild($product->getId());
			foreach($parentProducts as $parentProductId){
				$this->_pushProduct($parentProductId, $storeId);
			}
		}
		
		// Check should be removed
		if($product->getVisibility()==Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE){
			$deleteOnly = true;
		}
		if($product->getStatus()!=Mage_Catalog_Model_Product_Status::STATUS_ENABLED){
			$deleteOnly = true;
		}
		
		$queueModel = Mage::getSingleton('zolagosolrsearch/queue');
		/* @var $queueModel Zolago_Solrsearch_Model_Queue */
				

		if(empty($storeId) || $storeId==Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID){
			$storeIds = $product->getStoreIds();
		}else{
			$storeIds = array($storeId);
		}
		foreach($this->_filterStoreIds($storeIds) as $storeId){
			$cores = Mage::helper("zolagosolrsearch")->getCoresByStoreId($storeId);
			if(!is_array($cores) || !isset($cores[0])){
				continue;
			}
			
			$item = Mage::getModel("zolagosolrsearch/queue_item");
			/* @var $item Zolago_Solrsearch_Model_Queue_Item */

			$item->setProductId($product->getId());
			$item->setCoreName($cores[0]);
			$item->setStoreId($storeId);
			$item->setDeleteOnly((int)$deleteOnly);
			$queueModel->push($item);
		}

	}
	
	
	/**
	 * @param array $storeIds
	 * @return array
	 */
	protected function _filterStoreIds(array $storeIds) {
		return array_intersect($storeIds, 
			Mage::helper("zolagosolrsearch")->getAvailableStores());
	}
	
	
	/**
	 * @return array
	 */
	protected function _getChangableCategoryAttributes() {
		return array("is_active", "name", "include_in_menu", "is_anchor");
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
	
	/**
	 * If no after dispatch - handle collected in destruct
	 */
	public function __destruct() {
		if(!$this->_handled){
			$this->processCollectedProducts();
		}
	}
	
}

?>
