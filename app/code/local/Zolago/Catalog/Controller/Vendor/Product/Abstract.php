<?php
/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
class Zolago_Catalog_Controller_Vendor_Product_Abstract extends Zolago_Catalog_Controller_Vendor_Abstract
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
		return Mage::app()->getStore($storeId)->getId();
	}
	
	protected function _setCollectionOrder(Mage_Catalog_Model_Resource_Eav_Attribute $attribute, $dir) {
		if($attribute instanceof Mage_Catalog_Model_Resource_Eav_Attribute && $this->isAttributeEnumerable($attribute)){
			$source = $column->getAttribute()->getSource();
			if($source instanceof Mage_Eav_Model_Entity_Attribute_Source_Boolean){
				// Need fix
				Mage::getResourceSingleton('zolagocatalog/vendor_mass')->addBoolValueSortToCollection(
						$attribute,
						$this->getCollection(),
						$column->getDir()
				);
				return $this;
			}elseif($attribute->getFrontendInput()=="multiselect"){
				// Need fix - comma
				Mage::getResourceSingleton('zolagocatalog/vendor_mass')->addMultipleValueSortToCollection(
						$attribute,
						$this->getCollection(),
						$column->getDir()
				);
				return $this;
			}
		}
		
		$this->getCollection()->setOrder($attribute->getCode() . " " . $dir);
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


//		//Add Active Static Filters to Collection - Start
//		$staticFilters		= $this->getStaticFilters();
//		$staticFilterValues	= $this->getStaticFilterValues();
//		if ($staticFilters && $staticFilterValues) {
//			foreach ($staticFilters as $staticFilter) {
//				/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
//				$attribute = Mage::getModel('eav/entity_attribute')->load($staticFilter);
//				if ($attribute->getGridPermission() == Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::USE_IN_FILTER
//					&& array_key_exists($staticFilter, $staticFilterValues)) {
//					$collection->addAttributeToFilter($attribute->getAttributeCode(), $staticFilterValues[$staticFilter]);
//				}
//			}
//		}
		

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
			
			if($column->getIndex()=="thumbnail"){
				$collection->addImagesCount(false);
			}
			
			if($column->getIndex()=="name"){
				$collection->joinAttribute("status", 'catalog_product/status', 'entity_id', null, 'left');
			}
		}
		
		// Add images count 
		
		return $collection;
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



