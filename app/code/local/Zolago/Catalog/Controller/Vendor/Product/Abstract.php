<?php
/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
class Zolago_Catalog_Controller_Vendor_Product_Abstract 
	extends Zolago_Catalog_Controller_Vendor_Abstract
{
	
	/**
	 * @return Mage_Core_Model_Store
	 */
	protected function _getStore() {
		$storeId = Mage::app()->getRequest()->getParam("store");
		$candidate = Mage::app()->getStore($storeId);
		if($candidate->getId()==$storeId){
			return $candidate;
		}
		return Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE);
	}
	

	/**
	 * @return Zolago_Catalog_Model_Vendor_Product_Grid
	 */
	public function getGridModel() {
		return Mage::getSingleton('zolagocatalog/vendor_product_grid');
	}
	
	/**
	 * @return Mage_Eav_Model_Entity_Attribute_Set
	 */
	public function getAttributeSet() {
		return $this->getGridModel()->getAttributeSet();
	}
	
	/**
	 * @return array
	 */
	public function getColumns() {
		return $this->getGridModel()->getColumns();
	}
	
	
	/**
	 * @return int
	 */
	protected function _getStoreId() {
		$storeId = $this->getRequest()->getParam("store_id");
		return (int)Mage::app()->getStore($storeId)->getId();
	}

	/**
	 * @return int
	 */
	protected function _getLabelStoreId() {
		return $this->getGridModel()->getLabelStore()->getId();
	}
	
	/**
	 * collection dont use after load - just flat selects
	 * @param Varien_Data_Collection
	 * @return Varien_Data_Collection
	 */
	protected function _prepareCollection(Varien_Data_Collection $collection = null){
		
		if(!($collection instanceof Mage_Catalog_Model_Resource_Product_Collection)){
			$collection = Mage::getResourceModel("zolagocatalog/vendor_product_collection");
		}
		/* @var $collection Zolago_Catalog_Model_Resource_Vendor_Product_Collection */

		$collection->setFlag("skip_price_data", true);

		// Set store id
		$store = $this->getStore();
		$collection->setStoreId($store->getId());

		if($store->getId()){
			$collection->addStoreFilter($store);
		}

		// Add non-grid filters
		$collection->addAttributeToFilter("udropship_vendor", $this->getVendorId());
		$collection->addAttributeToFilter("attribute_set_id", $this->getAttributeSet()->getId());
		$collection->addAttributeToFilter("visibility", array("in"=>array(
			Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
			Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
			Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
		)));


		//Add Active Static Filters to Collection - Start
		$staticFilters = $this->getGridModel()->getStaticFilters();
		if (is_array($staticFilters) && count($staticFilters)) {
			foreach ($staticFilters as $staticFilter=>$value) {
				/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
				$attribute = $this->getGridModel()->getAttribute($staticFilter);
				if ($attribute->getGridPermission() == Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::USE_IN_FILTER && !empty($value)) {
					$collection->addAttributeToFilter($attribute->getAttributeCode(), $value);
				}
			}
		}
	
	    // Prepare collection data
		foreach($this->getColumns() as $key=>$column){
			$columnData = $column->getData();
			
			// Add regular dynamic attributes data
			if(isset($columnData['attribute']) &&
				$columnData['attribute'] instanceof Mage_Catalog_Model_Resource_Eav_Attribute){
				// By regular attribute
				$attributeCode = $columnData['attribute']->getAttributeCode();
				$collection->joinAttribute($attributeCode, 'catalog_product/'.$attributeCode, 'entity_id', null, 'left');
				//$collection->addAttributeToSelect($columnData['attribute']->getAttributeCode());
			}
			
			switch ($column->getIndex()) {
				case "thumbnail":
					$collection->addImagesCount(false);
				break;
				case "name":
					$collection->joinAttribute("skuv", 'catalog_product/skuv', 'entity_id', null, 'left');
				break;
			}
		}
		
		
		return $collection;
    }
	
	/**
	 * @param string $key
	 * @param mixed $value
	 * @return array
	 */
	protected function _getSqlCondition($key, $value) {
		
		$attribute=$this->getGridModel()->getAttribute($key);
				
		if(is_array($value)){
			$isDate = in_array($attribute->getFrontendInput(), 
					array("datetime", "date"));
			
			if(isset($value['to']) && is_numeric($value['to']) && !$isDate){
				$value['to'] = (float)$value['to'];
			}
			if(isset($value['from']) && is_numeric($value['from']) && !$isDate){
				$value['from'] = (float)$value['from'];
			}
			if($isDate){
				$value['date'] = true;
			}
			
			if(isset($value['to']) && is_numeric($value['to']) && 
					(!isset($value['from']) || (isset($value['from']) && $value['from']==0))){
				$value = array($value, array("null"=>true));
			}
			
			return $value;
		}
		
		if($attribute){
			// process name
			if($attribute->getAttributeCode()=="name"){
				$this->_getCollection()->addFieldToFilter(array(
						array("attribute"=>"name", "filter"=> array("like"=>"%".$value."%")),
						array("attribute"=>"skuv", "filter"=> array("like"=>"%".$value."%"))
				));
				return null;
			}
			// Proces enuberable attributes
			if($this->getGridModel()->isAttributeEnumerable($attribute)){
				// Process null
				if($value===self::NULL_VALUE){
					return array("null"=>true);
				}
				// Process multiply select
				if($attribute->getFrontendInput()=="multiselect"){
					/**
					 * Do id by MySQL RegExp expression in applaying filter in gird
					 */
					$collection = $this->_getCollection();

					$code = $attribute->getAttributeCode();
					$aliasCode = $code ."_filter";


					$collection->joinAttribute($aliasCode, "catalog_product/$code", "entity_id", null, "left");


					$valueTable1 = "at_".$aliasCode."_default";
					$valueTable2 = "at_".$aliasCode;

					if($collection->getStoreId()){
						$valueExpr = $collection->getSelect()->getAdapter()
							->getCheckSql("{$valueTable2}.value_id > 0", "{$valueTable2}.value", "{$valueTable1}.value");

					}else{
						$valueExpr = "$valueTable2.value";
					}
					// Try use regexp to match vales with boundary (like comma, ^, $)  - (123,456,678) 
					$collection->getSelect()->where(
							$valueExpr." REGEXP ?", "[[:<:]]".$value."[[:>:]]"
					);

					return null;
				}
				return array("eq"=>$value);
			}
		}
		
		// Return default
		return array("like"=>'%'.$value.'%');
	}
	
	/**
	 * @return array
	 */
	protected function _getAvailableQueryParams() {
		$out = array();
		foreach($this->getGridModel()->getColumns() as $column){
			if($column->getAttribute() && $column->getFilterable()!==false){
				$out[$column->getAttribute()->getAttributeCode()] = true;
			}
		}
		if(isset($out["thumbnail"])){
			$out["images_count"] = true;
		}
		return array_keys($out);
	}
	
	/**
	 * @return array
	 */
	protected function _getRestSort() {
		$sort = parent::_getRestSort();
		$attribute = null;
		
		if(isset($sort['order']) && isset($sort['dir'])){
			$attribute = $this->getGridModel()->getAttribute($sort['order']);
		}
		
		// Some special sort fixes
		if($attribute && $this->getGridModel()->isAttributeEnumerable($attribute)){
			$source = $attribute->getSource();
			$oldStoreId = $this->_getCollection()->getStoreId();
			if($source instanceof Mage_Eav_Model_Entity_Attribute_Source_Boolean){
				// Need fix 
				Mage::getResourceSingleton('zolagocatalog/vendor_mass')->
					addBoolValueSortToCollection(
						$attribute,
						$this->_getCollection(),
						$sort['dir']
				);
				return array();
			}elseif($source instanceof Mage_Eav_Model_Entity_Attribute_Source_Table){
				// Need fix - wrong sort - multiple values first
				if($attribute->getFrontendInput()=="multiselect"){
					Mage::getResourceSingleton('zolagocatalog/vendor_mass')->
						addMultipleValueSortToCollection(
							$attribute,
							$this->_getCollection(),
							$sort['dir']
					);
				// Need fix - need original values tot text values from eav!
				}else{
					$this->_getCollection()->setStoreId(
						$this->_getLabelStoreId()
					);
					Mage::getResourceSingleton('zolagocatalog/vendor_mass')->
						addEavTableSortToCollection(
							$attribute,
							$this->_getCollection(),
							$sort['dir']
					);
					$this->_getCollection()->setStoreId($oldStoreId);
					
				}
				return array();
			}
		}
		
		return $sort;
	}
	
	/**
	 * @return array
	 */
	protected function _getAvailableSortParams() {
		$out = array();
		foreach($this->getGridModel()->getColumns() as $column){
			if($column->getAttribute() && $column->getSortable()!==false){
				$out[] = $column->getAttribute()->getAttributeCode();
			}
		}
		return $out;
	}
	
	/**
	 * @param array $productIds
	 * @param array $attributes
	 * @param type $storeId
	 * @throws Mage_Core_Exception
	 */
	protected function _processAttributresSave(array $productIds, array $attributes, $storeId) {
		
		$collection = Mage::getResourceModel("zolagocatalog/vendor_product_collection");
		$inventoryData = array();
		
		// Vaild collection
		$collection->addAttributeToFilter('udropship_vendor', $this->getVendor()->getId());
		$collection->addIdFilter($productIds);
		
		if($collection->getSize()<count($productIds)){
			throw new Mage_Core_Exception("You are trying to edit not your product");
		}
		
		/* @var $collection Zolago_Catalog_Model_Resource_Vendor_Product_Collection */
		
		foreach($attributes as $attributeCode=>$value){
			if(!in_array($attributeCode, $collection->getEditableAttributes())){
				throw new Mage_Core_Exception("You are trying to edit not editable attribute (".htmlspecialchars($attributeCode).")");
			}
			
			// Process modified flow attributes
			switch($attributeCode){
				case "is_in_stock":
					$inventoryData['is_in_stock'] = $value;
					unset($attributes[$attributeCode]);
				break;
			
			}
		}
		
		
		$actionModel = Mage::getSingleton('catalog/product_action');
		/* @var $actionModel Mage_Catalog_Model_Product_Action */
		
		if($attributes){
			$actionModel->updateAttributes($productIds, $attributes, $storeId);
		}
		
		
		// Prepare stock
		foreach (Mage::helper('cataloginventory')->getConfigItemOptions() as $option) {
            if (isset($inventoryData[$option]) && !isset($inventoryData['use_config_' . $option])) {
                $inventoryData['use_config_' . $option] = 0;
            }
        }
		
		// Stock save
		if ($inventoryData) {
			/** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
			$stockItem = Mage::getModel('cataloginventory/stock_item');
			$stockItem->setProcessIndexEvents(false);
			$stockItemSaved = false;

			foreach ($productIds as $productId) {
				$stockItem->setData(array());
				$stockItem->loadByProduct($productId)
					->setProductId($productId);

				$stockDataChanged = false;
				foreach ($inventoryData as $k => $v) {
					$stockItem->setDataUsingMethod($k, $v);
					if ($stockItem->dataHasChangedFor($k)) {
						$stockDataChanged = true;
					}
				}
				if ($stockDataChanged) {
					$stockItem->save();
					$stockItemSaved = true;
				}
			}

			if ($stockItemSaved) {
				Mage::getSingleton('index/indexer')->indexEvents(
					Mage_CatalogInventory_Model_Stock_Item::ENTITY,
					Mage_Index_Model_Event::TYPE_SAVE
				);
			}
		}
		
	}
}



