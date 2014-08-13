<?php

class Zolago_Catalog_Block_Vendor_Price_Modal extends Mage_Core_Block_Template
{
	
	/**
	 * @return Mage_Catalog_Model_Product
	 */
	public function getProduct() {
		return Mage::registry("current_product");
	}
	
	/**
	 * @return array
	 */
	public function getPriceSourceOptions() {
		$priceType = Mage::getSingleton('eav/config')->getAttribute(
			Mage_Catalog_Model_Product::ENTITY,
			Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE
		);
		$priceType->setStoreId($this->getCurrentStoreId());
		
		return $priceType->getSource()->getAllOptions();
	}
	
	/**
	 * @param Mage_Catalog_Model_Product $product
	 * @return type
	 */
	public function getChildren(Mage_Catalog_Model_Product $product) {
		
		$resModel =  Mage::getResourceSingleton('zolagocatalog/vendor_price');
		/* @var $resModel Zolago_Catalog_Model_Resource_Vendor_Price */
		
		$details = $resModel->getDetails(array($product->getId()), $product->getStoreId(), false);
		
		if($details && isset($details[0]['children'])){
			return $this->_addConverterDataToChilds(
					$details[0]['children'], 
					$product->getStoreId()
			);
		}
		
		return array();
	}
	
	/**
	 * @return Zolago_Dropship_Model_Vendor
	 */
	protected function _getVendor() {
		return Mage::getSingleton('udropship/session')->getVendor();
	}
	
	/**
	 * @param array $children
	 * @param int $storeId
	 * @return array
	 */
	protected function _addConverterDataToChilds(array $children, $storeId) {
		
		foreach($children as $attrKey=>$attribute){
			$ids = array();
			foreach($attribute['children'] as $child){
				$ids[]=$child['product_id'];
			}

			$vendor = $this->_getVendor();

			$convertert = Mage::getSingleton('zolagoconverter/client');
			/* @var $convertert Zolago_Converter_Model_Client */

			$collection = Mage::getResourceModel("catalog/product_collection");
			/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
			$collection->addAttributeToSelect("skuv", "left");
			$collection->setStoreId($storeId);
			$collection->addIdFilter($ids);

			$converterData = array();

			foreach($collection as $product){
				$response = $convertert->getPrices($vendor->getExternalId(), $product->getSkuv());
				
				if($response){
					$converterData[$product->getId()] = $response;
				}
			}
			
			Mage::log($converterData);
			
			foreach($attribute['children'] as $key=>$child){
				if(isset($converterData[$child['product_id']])){
					$children[$attrKey]['children'][$key]['converters'] = $converterData[$child['product_id']];
				}
			}
		}
		
		return $children;
	}

}