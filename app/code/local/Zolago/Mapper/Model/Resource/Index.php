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
		$this->_clearIndex($products ? array("product_id"=>$products) : null);
		
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
			$filterWebsite[$filterWebsite] = true;
		}
		
		$mapperCollection = $this->_getMapperCollection();
		/* @var $mapperCollection Zolago_Mapper_Model_Resource_Mapper_Collection */
		$mapperCollection->addIsActiveFilter();
		
		if($filterWebsite){
			$mapperCollection->addFieldToFilter("website_id", array("in"=>array_keys($filterWebsite)));
		}
		if($productAttributeSet){
			$mapperCollection->addFieldToFilter("attribute_set_id", array("in"=>array_keys($filterAttributeSet)));
		}
		
		
		// Step 5: Start mappers and prepare index data
		$this->_resetData();
		foreach($mapperCollection as $mapper){
			/* @var $mapper Zolago_Mapper_Model_Mapper */
			$productIds = $mapper->getMatchingProductIds();
			$categoryIds = $mapper->getCategoryIds();
			$websiteId = $mapper->getWebsiteId();
			foreach ($categoryIds as $categoryId){
				foreach($productIds as $productId){
					$this->_prepareData($websiteId, $mapper->getId(), $categoryId, $productId);
				}
			}
		}
		
		return $this->_saveData();
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

