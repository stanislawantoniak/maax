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
	 * Shoul queue by handled
	 * @var bool
	 */
	
	protected $_canBeHandled = true;
	
	
	/**
	 * Process queue
	 */
	public function cronProcessQueue() {
		Mage::getSingleton('zolagosolrsearch/queue')->process();
	}
	
	
	/**
	 * Cleanup queue
	 */
	public function cronCleanupQueue() {
		Mage::getSingleton('zolagosolrsearch/queue')->cleanup();
	}
	
	
	/**
	 * Process converter stock save
	 * @param Varien_Event_Observer $observer
	 */
	public function zolagoCatalogConverterStockSaveBefore(
			Varien_Event_Observer $observer) {
		
		$this->collectProduct($observer->getEvent()->getProductId());
	}
	
	/**
	 * After all stock changed - process collected products
	 * @param Varien_Event_Observer $observer
	 */
	public function zolagoCatalogConverterStockComplete(
			Varien_Event_Observer $observer) {
		
		$this->processCollectedProducts();
	}
	
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
		$this->_canBeHandled = false;
		$this->_pushProduct($product, $product->getStoreId(), true, true);
		
	}
	
	
	/**
	 * After commit product delte.
	 * @param Mage_Core_Model_Observer $observer
	 * @return type
	 */
	public function catalogProductDeleteAfter(Varien_Event_Observer $observer) {
		$this->_canBeHandled = true;
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
		
		$this->_pushProduct($product->getId(), $product->getStoreId(), true);
		
	}

	
	/**
	 * Collect affected products ids
	 * @param Varien_Event_Observer $observer
	 */
	public function catalogruleApplyAfter(Varien_Event_Observer $observer)
	{
		/**
		 * Important
		 * @todo add apply rule with disabled status before rule delte or delete form caralog_rule_product_price
		 * Then should work - now prices are from Magento not solr in listing
		 * Catalog rule price applied - queue matched products
		 */
		
		/*
		$affectedProductsIds = Mage::getResourceModel("zolagosolrsearch/improve")
				->getRuleAppliedAffectedProducts();
		$availableStores = $this->_filterStoreIds(array_keys(Mage::app()->getStores()));

		foreach ($affectedProductsIds as $productId)
		{
			if ($productId > 0) {
				foreach($availableStores as $storeId){
					$this->collectProduct($productId, $storeId);
				}
			}
		}
		*/
	}
	

	/**
	 * Before category Delete
	 * @param Varien_Event_Observer $observer
	 */
	public function catalogCategoryDeleteBefore(Varien_Event_Observer $observer) {
		$category = $observer->getEvent()->getCategory();
		/* @var $category Mage_Catalog_Model_Category */
		$category->getStoreId(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
		
		////////////////////////////////////////////////////////////////////////
		// Process scopes
		////////////////////////////////////////////////////////////////////////		
		$storeIds = $category->getStoreIds();
		
		foreach($this->_filterStoreIds($storeIds) as $storeId){
			$regualrIds = $category->
				setStoreId($storeId)->
				getProductCollection()->
					// Filter only product visible and enabled in current category in scope
					addAttributeToFilter("status", Mage_Catalog_Model_Product_Status::STATUS_ENABLED)->
					addAttributeToFilter("visibility",
						array("neq"=>Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))->
					getAllIds();
			
			foreach($regualrIds as $productId){
				$this->collectProduct($productId, $category->getStoreId());
			}
		}
		
		$this->_canBeHandled = false;
	}
	
	
	/**
	 * Category delete after commit
	 * @param Varien_Event_Observer $observer
	 */
	public function catalogCategoryDeleteAfter(Varien_Event_Observer $observer) {
		$this->_canBeHandled = true;
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
		
		foreach($productIds as $productId){
			$this->collectProduct($productId, $storeId, true);
		}
		
		$this->processCollectedProducts();
	}
	
	
	/**
	 * After mapper assign products
	 * @param Varien_Event_Observer $observer
	 */
	public function zolagoMapperAfterAssignProducts(
			Varien_Event_Observer $observer) {


		$event = $observer->getEvent();
		$productIds = $event->getProductIds();
	
		foreach($productIds as $productId){
			$this->collectProduct($productId, Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID, true);
		}
		
		$this->processCollectedProducts();
		
	}

    public function zolagoCatalogAfterUpdateProducts(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $productIds = $event->getProductIds();

		foreach ($productIds as $productId) {
			$this->collectProduct($productId, Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
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
	}

	public function catalogInventorySave(Varien_Event_Observer $observer)
	{
		$event = $observer->getEvent();
		$_item = $event->getItem();
		$productId = $_item->getData("product_id");

		$isInStock = (int)$_item->getData("is_in_stock");
		$type_id = $_item->getData("type_id");

		if ($type_id == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE && $isInStock == 0) {
			$_product = Mage::getModel("catalog/product")->load($productId);
			$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($_product->getId());

			if (!empty($parentIds) && isset($parentIds[0])) {
				$this->collectProduct($productId);
				$this->processCollectedProducts();
			}
		}
	}
	/**
	 * Process prices after catalog update via converter
	 * @param Varien_Event_Observer $observer
	 */
	public function catalogConverterPriceUpdateAfter(Varien_Event_Observer $observer) {
		$this->collectProductsAndPushToQueue($observer);
	}

    /**
     * Push to solr
     * Collect product (as ids) and push to solr queue
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function collectProductsAndPushToQueue(Varien_Event_Observer $observer) {
        $event        = $observer->getEvent();
        $productIds   = $event->getProductIds();
        $storeId      = $event->hasData('store_id') ? $event->getData('store_id') : Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
        $checkParents = $event->hasData('check_parents') ? $event->getData('check_parents') : false;

        foreach ($productIds as $productId) {
            $this->collectProduct($productId, $storeId, $checkParents);
        }

        $this->processCollectedProducts();
        return $this;
    }
	
	/**
	 * @param int|Mage_Catalog_Model_Product $product
	 * @param type $storeId
	 */
	public function collectProduct($product, 
		$storeId=Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID, 
		$checkParents=false) {
		
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

		$oldStore = Mage::app()->getStore();
		if(!$this->_canBeHandled){
			return;
		}
		
		$resource = Mage::getResourceModel("zolagosolrsearch/improve");;
		/* @var $resource Zolago_Solrsearch_Model_Resource_Improve */
	
		foreach($this->_collectedProdutcs as $storeId=>$products){
			Mage::app()->setCurrentStore($storeId);
			$stores = array();
			$childsIds = array();
			$parentIdsFlat = array();
			
			foreach($products as $productId){
				if(isset($this->_collectedCheckParents[$storeId][$productId])){
					$childsIds[] = $productId;
				}
			}
			
			if($childsIds){
                $parentIdsFlat = $resource->getParentIdsByChild($childsIds, true);
			}

            /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
            $collection = Mage::getResourceModel('catalog/product_collection');
			$collection->addAttributeToSelect(array("status", "visibility"), 'left');
			
			if($storeId!=Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID){
				$collection->addStoreFilter($storeId);
				$collection->setStoreId($storeId);
			}else{
				$stores = $resource->getStoreIdsByProducts($products);
			}
			
			$collection->addIdFilter($products + $parentIdsFlat);

            // Load data from sql is faster than load object
            $productData = $collection->getData();
            foreach ($productData as &$product) {
                if (isset($stores[$product['entity_id']])) {
                    $product['store_ids'] = $stores[$product['entity_id']];
                }
            }
			Mage::app ()->setCurrentStore ( $oldStore );
            $this->_pushMultipleProducts($productData, $storeId);
		}
		
		$this->_collectedProdutcs = array();
		$this->_collectedCheckParents = array();

    }

    /**
     * Multiple products push do queue
     *
     * @param array $productData
     * @param $storeId
     */
    protected function _pushMultipleProducts(array $productData, $storeId) {
        /* @var $queueModel Zolago_Solrsearch_Model_Queue */
        $queueModel = Mage::getSingleton('zolagosolrsearch/queue');
        /** @var Zolago_Solrsearch_Helper_Data $zssHelper */
        $zssHelper = Mage::helper("zolagosolrsearch");

        $queueItems = array();
        foreach ($productData as $product) {

            $deleteOnly = false;
            // Check should be removed
            if ($product['visibility'] == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
                $deleteOnly = true;
            }
            if ($product['status'] != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                $deleteOnly = true;
            }

            if (empty($storeId) || $storeId == Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
                $storeIds = empty($product['store_ids'])? array():$product['store_ids'];
            } else {
                $storeIds = array($storeId);
            }
            /* @var $item Zolago_Solrsearch_Model_Queue_Item */
            $rawItem = Mage::getModel("zolagosolrsearch/queue_item");

            foreach ($this->_filterStoreIds($storeIds) as $_storeId) {
                $cores = $zssHelper->getCoresByStoreId($_storeId);
                if (!is_array($cores) || !isset($cores[0])) {
                    continue;
                }
                foreach ($cores as $core) {
                    $item = clone $rawItem;
                    $item->setProductId($product['entity_id']);
                    $item->setCoreName($core);
                    $item->setStoreId($_storeId);
                    $item->setDeleteOnly((int)$deleteOnly);
                    $item->setStatus(Zolago_Solrsearch_Model_Queue_Item::STATUS_WAIT);
                    $queueItems[$product['entity_id'] . '_' . $core . '_' . $_storeId . '_' . (int)$deleteOnly] = $item;
                }
            }
        }
        $queueModel->pushMultiple($queueItems);
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
			
			foreach($cores as $core){
				$item = Mage::getModel("zolagosolrsearch/queue_item");
				/* @var $item Zolago_Solrsearch_Model_Queue_Item */

				$item->setProductId($product->getId());
				$item->setCoreName($core);
				$item->setStoreId($storeId);
				$item->setDeleteOnly((int)$deleteOnly);
				$queueModel->push($item);
			}
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

	public function handleCatalogLayoutRender($observer)
	{
	    if(Mage::getModel('zolagosolrsearch/catalog_product_list')->getMode() === Zolago_Solrsearch_Model_Catalog_Product_List::MODE_CATEGORY){
			
			$replaceCatalogLayerNavigation = (int) Mage::Helper('solrsearch')->getSetting('replace_catalog_layer_nav');
		    if ($replaceCatalogLayerNavigation > 0)
		    {
		        $layoutUpdate = Mage::getSingleton('core/layout')->getUpdate();
		        if ($category = Mage::registry('current_category') && !Mage::registry('current_product'))
		        {
		            $layoutUpdate->addHandle('solrbridge_solrsearch_category_view');
		        }
		    }
	    }
	}
	public function checkRelatedCategoryProducts($observer) {
	    $category = $observer->getEvent()->getCategory();
	    $ids = $category->getRelatedProductsToRebuild();
	    if (empty($ids)) {
	        return;
	    }
	    foreach ($ids as $id) {
	        $this->collectProduct($id);
	    }
	    $this->processCollectedProducts();
	}
	
}
