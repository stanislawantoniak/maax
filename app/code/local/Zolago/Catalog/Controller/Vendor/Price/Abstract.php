<?php
/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
class Zolago_Catalog_Controller_Vendor_Price_Abstract extends Zolago_Dropship_Controller_Vendor_Abstract
{

	
	/**
	 * collection dont use after load - just flat selects
	 * @param Mage_Catalog_Model_Resource_Product_Collection
	 * @return Zolago_Catalog_Model_Resource_Vendor_Price_Collection
	 */
	protected function _prepareCollection(Mage_Catalog_Model_Resource_Product_Collection $collection=null) {
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
	protected function _processAttributresSave(array $productIds, array $attributes, $storeId) {
		
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
				throw new Mage_Core_Exception("You are trying to edit not editable attribute");
			}
			
			// Process modified flow attributes
			switch($attributeCode){
				case "display_price":
					$attributes['price'] = $value;
					unset($attributes[$attributeCode]);
				break;
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
	
	/**
	 * @return int
	 */
	protected function _getStoreId() {
		$storeId = $this->getRequest()->getParam("store_id");
		$store = Mage::app()->getStore($storeId);
		
		$allowedStores = $this->getAllowedStores();
		
		foreach($allowedStores as $_store){
			if($_store->getId()==$store->getId()){
				return $store->getId();
			}
		}
		
		throw new Mage_Core_Exception("Unknow store");
	}
	
	/**
	 * @return array
	 */
	public function getAllowedStores() {
		return Mage::helper("zolagodropship")->getAllowedStores($this->getVendor());
	}

	/**
	 * @return Unirgy_Dropship_Model_Vendor
	 */
	public function getVendor() {
		return $this->_getSession()->getVendor();
	}
	

}



