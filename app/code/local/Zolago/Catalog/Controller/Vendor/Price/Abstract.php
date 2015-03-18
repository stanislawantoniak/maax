<?php
/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
class Zolago_Catalog_Controller_Vendor_Price_Abstract 
	extends Zolago_Catalog_Controller_Vendor_Abstract
{

	/**
	 * @return array
	 */
	protected function _getAvailableSortParams() {
		return $this->_getCollection()->getAvailableSortParams();
	}
	
	/**
	 * @return array
	 */
	protected function _getAvailableQueryParams() {
		return $this->_getCollection()->getAvailableQueryParams();
	}
	
	/**
	 * @param string $key
	 * @param mixed $value
	 * @return mixed
	 */
	protected function _getSqlCondition($key, $value) {
		if(is_array($value)){
			
			if(isset($value['to']) && is_numeric($value['to'])){
				$value['to'] = (float)$value['to'];
			}
			if(isset($value['from']) && is_numeric($value['from'])){
				$value['from'] = (float)$value['from'];
			}
			
			if(isset($value['to']) && is_numeric($value['to']) && 
					(!isset($value['from']) || (isset($value['from']) && $value['from']==0))){
				$value = array($value, array("null"=>true));
			}
			
			return $value;
		}
		switch ($key) {
			case "is_new":
			case "is_bestseller":
				return $value==1 ? array("eq"=>$value) : array(array("null"=>true), array("eq"=>$value));
			break;
			case "product_flags":
			case "is_in_stock":
				return array("eq"=>$value);
			break;
			case "converter_price_type":
			case "converter_msrp_type":
				return $value!=0 ? array("eq"=>$value) : array("null"=>true);
			break;
			case "msrp":
				return $value==1 ? array("notnull"=>true) : array(array("null"=>true));
			break;
		}
		return array("like"=>'%'.$value.'%');
	}
	
	
	
	/**
	 * collection dont use after load - just flat selects
	 * @param Mage_Catalog_Model_Resource_Product_Collection
	 * @return Zolago_Catalog_Model_Resource_Vendor_Price_Collection
	 */
	protected function _prepareCollection(Varien_Data_Collection $collection=null) {
		$visibilityModel = Mage::getSingleton("catalog/product_visibility");
		/* @var $visibilityModel Mage_Catalog_Model_Product_Visibility */
		
		if(!($collection instanceof Mage_Catalog_Model_Resource_Product_Collection)){
			$collection = Mage::getResourceModel("zolagocatalog/vendor_price_collection");
		}
		/* @var $collection Zolago_Catalog_Model_Resource_Vendor_Price_Collection */
		
		$collection->setStoreId($this->_getStoreId());
		
		
		// Filter visible
		$collection->addAttributeToFilter("visibility", 
				array("neq"=>$visibilityModel::VISIBILITY_NOT_VISIBLE), "inner");
		
		// Filter dropship
		$collection->addAttributeToFilter("udropship_vendor", $this->getVendor()->getId(), "inner");
		
		
		return $collection;
	}
	
	/**
	 * @param array $productIds
	 * @param array $attributes
	 * @param type $storeId
	 * @throws Mage_Core_Exception
	 */
	protected function _processAttributresSave(array $productIds, array $attributes, $storeId, array $data) {
		
		$collection = Mage::getResourceModel("zolagocatalog/vendor_price_collection");
		$inventoryData = array();
		
		// Vaild collection
		$collection->addAttributeToFilter('udropship_vendor', $this->getVendor()->getId());
		$collection->addIdFilter($productIds);
		
		if($collection->getSize()<count($productIds)){
			throw new Mage_Core_Exception("You are trying to edit not your product");
		}
		
		/* @var $collection Zolago_Catalog_Model_Resource_Vendor_Price_Collection */
		
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
            Mage::log('_processAttributresSave', null, 'attributes_log.log');
			/** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
//			$stockItem = Mage::getModel('cataloginventory/stock_item');
//			$stockItem->setProcessIndexEvents(false);
//			$stockItemSaved = false;
//
//			foreach ($productIds as $productId) {
//				$stockItem->setData(array());
//				$stockItem->loadByProduct($productId)
//					->setProductId($productId);
//
//				$stockDataChanged = false;
//				foreach ($inventoryData as $k => $v) {
//					$stockItem->setDataUsingMethod($k, $v);
//					if ($stockItem->dataHasChangedFor($k)) {
//						$stockDataChanged = true;
//					}
//				}
//				if ($stockDataChanged) {
//					$stockItem->save();
//					$stockItemSaved = true;
//				}
//			}
//
//			if ($stockItemSaved) {
//				Mage::getSingleton('index/indexer')->indexEvents(
//					Mage_CatalogInventory_Model_Stock_Item::ENTITY,
//					Mage_Index_Model_Event::TYPE_SAVE
//				);
//			}
		}
	}
}



