<?php
/**
 * resource model for mapper queue
 */
class Zolago_Mapper_Model_Resource_Index extends Mage_Core_Model_Resource_Db_Abstract {
	
	protected $_buffer = 500;
	protected $_dataToSave = array();
	
    protected function _construct() {
		// Mapper id is not primary, but there is no id-required
        $this->_init('zolagomapper/index','mapper_id');
    }

	/**
	 * @param Zolago_Mapper_Model_Mapper $mapper
	 * @return array
	 */
	public function getProductIdsByMapper(Zolago_Mapper_Model_Mapper $mapper) {
		$select = $this->getReadConnection()->select()
				->from($this->getMainTable(), array("product_id"))
				->where("mapper_id=?", $mapper->getId())
				->group("product_id");
		return $this->getReadConnection()->fetchCol($select);
	}
	
    /**
     *  reindex by mapper list
     */	
    public function reindexForMappers($mappers = null, $websiteId = null) {
		// Step 1: get mapper ids
		if (!is_array($mappers) && $mappers!==null) {
			$mappers = array($mappers);
		}
		// Step 2: Clear index
		$conds = array();
		if ($mappers) {
			$conds['mapper_id'] = $mappers;
		}
		if ($websiteId) {
			$conds['website_id'] = $websiteId;
		}
		
		$this->_clearIndex($conds? $conds:null);
		$mapperCollection = $this->_getMapperCollection();
		if ($websiteId) {
			$mapperCollection->addFieldToFilter("website_id", $websiteId);
		}
		if ($mappers) {
			$mapperCollection->addFieldToFilter("mapper_id", array("in"=>array_values($mappers)));
		}
		$this->_mapperCollection = $mapperCollection;
		return $this->_reindexMappers();
		
    }

	/**
	 * Assign products to catalog category
	 * Remove old outdated
	 * Insert to new one
	 * Return true if no errors with mappers
	 * Return false if any problems with mappers
	 * errors messages saved in registry -> zolago_mapper_error
	 *
	 * @param null $productsIds
	 * @return bool
	 * @throws Exception
	 */
	public function assignWithCatalog($productsIds=null) {
		$filter = $productsIds ? array("product_id"=>$productsIds) : null;
		$templateProd = Mage::getModel("catalog/product");
		$currentIndexAssign = $this->getCurrentIndexAssign($filter);
		$currentCatalogAssign = $this->getCurrentCatalogAssign($filter);
		$indexer = Mage::getSingleton("index/indexer");
		/* @var $indexer Mage_Index_Model_Indexer */
		$affectedProductIds = array();
		
		if(!$productsIds){
			$productsIds = Mage::getResourceModel("catalog/product_collection")->getAllIds();
		}
		$categoryList = array();
		foreach($productsIds as $productId){
			$old = array();
			if(isset($currentCatalogAssign[$productId])){
				$old = $currentCatalogAssign[$productId];
			}
			$new = array();
			if(isset($currentIndexAssign[$productId])){
				$new = $currentIndexAssign[$productId];
			}
			$toDelete = array_diff($old, $new);
			$toInsert = array_diff($new, $old);
			
			// No changes
			if(empty($toDelete) && empty($toInsert)){
				continue;
			}
			
			
			if(!empty($toDelete)){
   				Mage::getSingleton('core/resource')->getConnection('core_write')->delete(
					$this->getTable('catalog/category_product'),
						$this->getReadConnection()->quoteInto("product_id=?", $productId) .
						" AND ".
						$this->getReadConnection()->quoteInto("category_id IN (?)", $toDelete) 
				);
                foreach ($toDelete as $item) {
                    $categoryList[$item] = $item;
                }
			}
			foreach($toInsert as $insertId){
				Mage::getSingleton('core/resource')->getConnection('core_write')->insert(
					$this->getTable('catalog/category_product'),
					array("product_id"=>$productId, "category_id"=>$insertId, "position"=>1)
				);
				$categoryList[$insertId] = $insertId;
			}
			
			// Run indexer if nessesery
			if (!empty($toInsert) || !empty($toDelete)) {
				$templateProd->setId($productId);
				$templateProd->setCategoryIds($new);
				//$object->setAffectedCategoryIds(array_merge($toInsert, $toDelete));
				$templateProd->setIsChangedCategories(true);
				
				
				$affectedProductIds[] = $productId;
			}
		}
        // reindex categories
        $templateCategory = Mage::getModel("catalog/category");
        foreach ($categoryList as $categoryId) {
            $templateCategory->setId($categoryId);
            $templateCategory->setIsChangedProductList(true);
			// Process event index
				
    		$indexer->processEntityAction(
					$templateCategory, 
					Mage_Catalog_Model_Category::ENTITY, 
					Mage_Index_Model_Event::TYPE_SAVE
			);            
        }
//		$event = Mage::getModel("index/event");
//		/* @var $newData Mage_Index_Model_Event */
//		$newData = array("product_ids"=>$affectedProductIds);
//		$event->setNewData($newData);
//		
//		// Process normal indexer
//		Mage::getResourceSingleton("catalog/category_indexer_product")
//			->catalogProductMassAction($event);

		if (!empty($categoryList)) {
			Mage::dispatchEvent("zolago_mapper_after_assign_products", array(
				"product_ids" => $affectedProductIds
			));
		}
		if (Mage::registry('zolago_mapper_error')) {
			// There was some error
			return false;
		}
		return true;
		
	}
	
	
	/**
	 * @param array $mapper set of mappers
	 * @return array
	 */
    public function getAssignedProducts($mapper) {
    	if (!is_array($mapper) && !empty($mapper)) {
    		$mapper = array($mapper);
    	}
    	$adapter = $this->getReadConnection();
		$select = $adapter->select()
			->distinct()
			->from(
				$this->getTable('zolagomapper/index'),
				array('product_id'))
			->where($adapter->quoteInto('mapper_id ' . (is_scalar($mapper) ? " = ?" : "IN (?)"), $mapper));

		$out = array();
		foreach ($adapter->fetchAssoc($select) as $item) {
			$out[$item['product_id']] = $item['product_id'];
		}
		return $out;
    }
	/**
	 * @param type $filter
	 * @return array - array(prodId=>array(catId1, catId2, ...);
	 */
	protected function getCurrentCatalogAssign($filter){
		$adapter = $this->getReadConnection();
		$select = $adapter->select()->from(
				$this->getTable('catalog/category_product'), 
				array('product_id', 'category_id'
		));
		if($filter && isset($filter['product_id'])){
            $select->where('product_id IN (?)', $filter['product_id']);
		}
		$out = array();
		$result = $this->getReadConnection()->fetchAll($select);
		foreach($result as $item){
			if(!isset($out[$item['product_id']])){
				$out[$item['product_id']] = array();
			}
			$out[$item['product_id']][] = $item['category_id'];
		}
		return $out;
	}
	

	/**
	 * @param mixed $params
	 * @return array - array(prodId=>array(catId1, catId2, ...);
	 */
	public function getCurrentIndexAssign($params=null) {
		$select = $this->getReadConnection()->select();
		$select->from($this->getMainTable(), array("*"));
		if(is_array($params)){
			foreach($params as $field=>$value){
				$select->where($this->getReadConnection()->quoteInto(
					$field . " " . (is_scalar($value) ? "=?" : "IN(?)"), $value)
				);
			}
		}
		$select->group(array("product_id", "category_id"));
		
		$out = array();
		$result = $this->getReadConnection()->fetchAll($select);
		foreach($result as $item){
			
			if(!isset($out[$item['product_id']])){
				$out[$item['product_id']] = array();
			}
			$out[$item['product_id']][] = $item['category_id'];
		}
		return $out;
	}
	
	/**
	 * @param Mage_Catalog_Model_Resource_Product_Collection | array | int | null
	 */
	public function reindexForProducts($products=null, $websiteId=null) {
		// Step 1: get products ids
		if($products instanceof Mage_Catalog_Model_Resource_Product){
			$products = $products->getAllIds();
		}elseif(!is_array($products) && $products!==null){
			$products = array($products);
		}
		
		// Step 2: Clear index
		$conds = array();
		if ($products) {
			$conds["product_id"] = $products;
		}
		if ($websiteId) {
			$conds["website_id"] = $websiteId;
		}
		
		$this->_clearIndex($conds ? $conds:null);
		
		// Step 3: Load product-attribute set relations
		$productAttributeSet = $this->_getProductsAttributeSets($products);
		
		// Step 4: collect Mappers filters
		$filterWebsite = array();
		$filterAttributeSet = array();
		
		// Run for all avaialble webistes
		if($websiteId==null){		
			// Load product-website relations
			$productWebsite = $this->_getProductWebsites($products);
			foreach($productWebsite as $productId=>$websiteIds){
				if(isset($productAttributeSet[$productId])){
					$filterAttributeSet[$productAttributeSet[$productId]] = true;
				}
				foreach($websiteIds as $websiteId){
					$filterWebsite[$websiteId] = true;
				}
			}
		// Run for specified website
		}else{
			foreach($products as $productId){
				if(isset($productAttributeSet[$productId])){
					$filterAttributeSet[$productAttributeSet[$productId]] = true;
				}
			}
			$filterWebsite[$websiteId] = true;
		}
		
		$mapperCollection = $this->_getMapperCollection();
		/* @var $mapperCollection Zolago_Mapper_Model_Resource_Mapper_Collection */
		$mapperCollection->addIsActiveFilter();
		
		if($filterWebsite){
			$mapperCollection->addFieldToFilter("website_id", 
				array("in"=>array_keys($filterWebsite))
			);
		}
		if($productAttributeSet){
			$mapperCollection->addFieldToFilter("attribute_set_id", 
				array("in"=>array_keys($filterAttributeSet))
			);
		}

		$this->_mapperCollection = $mapperCollection;
		return $this->_reindexMappers();		
	}
	
    /**
     * reindex mappers
     */
    protected function _reindexMappers() {
    	
		$mapperCollection = $this->_mapperCollection;
		// Step 5: Start mappers and prepare index data
		$this->_resetData();
		$affectedIds = array();
		foreach($mapperCollection as $mapper){
			/* @var $mapper Zolago_Mapper_Model_Mapper */
			$productIds = $mapper->getMatchingProductIds();
			$affectedIds = array_merge($productIds, $affectedIds);
			$categoryIds = $mapper->getCategoryIds();
			$websiteId = $mapper->getWebsiteId();
			foreach ($categoryIds as $categoryId){
				foreach($productIds as $productId){
					$this->_prepareData($websiteId, $mapper->getId(), $categoryId, $productId);
				}
			}
		}
		$this->_saveData();
		return array_unique($affectedIds);
    }
	
	/**
	 * @return insert index values
	 */
	protected function _saveData() {
		$i = $this->_buffer;
		$all = 0;
		$insert = array();
		$this->_getWriteAdapter()->beginTransaction();
		foreach($this->_dataToSave as $item){
			$insert[] = $item;
			$i--;
			// Insert via buffer
			if($i==0){
				$i = $this->_buffer;
				$all += $this->_buffer;
				$this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $insert, array());
				$insert = array();
			}
		}
		// Insert out of buffer values
		if(count($insert)){
			$all += count($insert);
			$this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $insert, array());
		}
		
		// Commit transaction
		$this->_getWriteAdapter()->commit();
		$this->_resetData();
		return $all;
	}
	
	protected function _resetData() {
		$this->_dataToSave = array();
	}
	
	protected function _prepareData($websiteId, $mapperId, $categoryId, $productId) {
		$key =  $this->_buildIndexKey($websiteId, $mapperId, $categoryId, $productId);
		$this->_dataToSave[$key] = array(
			"website_id" => $websiteId, 
			"mapper_id" => $mapperId, 
			"category_id" => $categoryId, 
			"product_id" => $productId
		);
	}
	
	/**
	 * @param int $websiteId
	 * @param int $mapperId
	 * @param int $categoryId
	 * @param int $productId
	 * @return string
	 */
	protected function _buildIndexKey($websiteId, $mapperId, $categoryId, $productId) {
		return "$websiteId|$mapperId|$categoryId|$productId";
	}
	
	/**
	 * @return Zolago_Mapper_Model_Resource_Mapper_Collection
	 */
	protected function _getMapperCollection() {
		return Mage::getResourceModel('zolagomapper/mapper_collection');
	}


	/**
	 * @param array|null $productIds
	 * @return array
	 */
	protected function _getProductsAttributeSets($productIds=null) {
		$prodcutTable = $this->getTable("catalog/product");
		$select = $this->getReadConnection()->select();
		$select->from(
				array("product"=>$prodcutTable), 
				array("entity_id", "attribute_set_id")
		);
		if(is_array($productIds)){
			$select->where("product.entity_id IN (?)", $productIds);
		}
		return $this->getReadConnection()->fetchPairs($select);
	}
	
	/**
	 * retive:
	 * array(
	 *		productId => array (websiteId1, websiteId2, websiteId3...),
	 *		...
	 * )
	 * @param array|null $productIds
	 * @return array
	 */
	protected function _getProductWebsites($productIds=null) {
		$productWebsiteTable = $this->getTable("catalog/product_website");
		$select = $this->getReadConnection()->select();
		$select->from(
				array("product_website"=>$productWebsiteTable), 
				array("product_id", "website_id")
		);
		if(is_array($productIds)){
			$select->where("product_website.product_id IN (?)", $productIds);
		}
		$out = array();
		$result = $this->getReadConnection()->query($select);
		while($row = $result->fetch()){
			if(!isset($out[$row['product_id']])){
				$out[$row['product_id']] = array();
			}
			$out[$row['product_id']][] = $row['website_id'];
		}
		return $out;
	}

	/**
	 * @param null $params
	 * @return bool
	 */
	protected function _clearIndex($params = null) {
		if(is_array($params)){
			$conds = array();
			foreach($params as $field=>$value){
				$conds[] = $this->getReadConnection()->quoteInto($field . " " . (is_scalar($value) ? "=?" : "IN(?)"), $value);
			}
			$this->_getWriteAdapter()->delete($this->getMainTable(), implode(" AND ", $conds));
		}else{
			$this->_getWriteAdapter()->delete($this->getMainTable());
		}
		return true;
	}
}

