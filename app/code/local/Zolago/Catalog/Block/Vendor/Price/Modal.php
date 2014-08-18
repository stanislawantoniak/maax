<?php

class Zolago_Catalog_Block_Vendor_Price_Modal extends Zolago_Catalog_Block_Vendor_Price_Abstract
{
	
	/**
	 * @return array
	 */
	public function getPriceSourceOptions() {
		
		$convertert = Mage::getSingleton('zolagoconverter/client');
		/* @var $convertert Zolago_Converter_Model_Client */
		
		$vendor = $this->_getVendor();
		$product = $this->getProduct();
		
		$priceType = Mage::getSingleton('eav/config')->getAttribute(
			Mage_Catalog_Model_Product::ENTITY,
			Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE
		);
		$priceType->setStoreId($this->getCurrentStoreId());
		
		$options = $priceType->getSource()->getAllOptions();
		
		$reponse = null;
		
		if($product->isComposite()){
			try{
				$response = $convertert->getPrices(
					$vendor->getExternalId(), 
					$product->getSkuv()
				);
			}catch(Exception $e){

			}
		}
		
		foreach($options as &$option){
			if($option['value']==""){
				$option['price'] = $product->getPrice();
			}elseif($reponse && isset($reponse[$option['label']])){
				$option['price'] = $reponse[$option['label']];
			}else{
				$option['price'] = "";
			}
		}
		return $options;
	}
	
	/**
	 * @param Mage_Catalog_Model_Product $product
	 * @return array
	 */
	public function getChildren(Mage_Catalog_Model_Product $product) {
		
		if(!$this->hasData('children')){
			$resModel =  Mage::getResourceSingleton('zolagocatalog/vendor_price');
			/* @var $resModel Zolago_Catalog_Model_Resource_Vendor_Price */

			$details = $resModel->getDetails(array($product->getId()), $product->getStoreId(), false);

			$children = array();
			
			if($details && isset($details[0]['children'])){
				$children = $this->_addConverterDataToChilds(
						$details[0]['children'], 
						$product->getStoreId()
				);
			}
			
			$this->setData("children", $children);
		}
		return $this->getData('children');
	}
	
	/**
	 * @return array
	 */
	public function getPriceTyps() {
		return array(
			"A"	=>"Price A", 
			"B"	=>"Price B", 
			"C"	=>"Price C", 
			"Z"	=>"Price Z", 
			"salePriceBefore"	
				=> "MSRP", 
			"marketPrice"		
				=> "Market pr."
		);
	}
	
	/**
	 * @param type $index
	 * @return array
	 */
	public function getMinimalPrices($index=null) {
		if(!$this->hasData("minimal_prices")){
			$prices = array();
			$ignorePrices = array();
			foreach($this->getChildren($this->getProduct()) as $attribute){
				foreach($attribute['children'] as $child){
					if(!isset($child['converters'])){
						$prices = array();
						// Some row have no prices - break all
						break 2;
					}

					foreach($child['converters'] as $type=>$price){
						if(is_null($price) || $price==="" || $price===0){
							// Some price is not set - skip whole price group
							$ignorePrices[$type] = true;
						}
						$prices[$type][] = $price;
					}
				}
				foreach($prices as $key=>&$group){
					$prices[$key] = ($group && !isset($ignorePrices[$key])) ? min($group) : null;
				}
			}
			$this->setData("minimal_prices", $prices);
		}
		return $this->getData("minimal_prices", $index);
	}
	
	/**
	 * @param string $priceType
	 * @return float
	 */
	public function getMinimalPrice($priceType) {
		return $this->getMinimalPrices($priceType);
	}
	
	/**
	 * @param array $child
	 * @param string $priceType
	 * @return float|null
	 */
	public function getConverterPrice(array $child, $priceType) {
		if(isset($child['converters']) && isset($child['converters'][$priceType])){
			return $child['converters'][$priceType];
		}
		return null;
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
				try{
					$response = $convertert->getPrices(
						$vendor->getExternalId(), 
						$product->getSkuv()
					);
					if($response){
						$converterData[$product->getId()] = $response;
					}
				}catch(Exception $e){
					
				}
			}
			
			foreach($attribute['children'] as $key=>$child){
				if(isset($converterData[$child['product_id']])){
					$children[$attrKey]['children'][$key]['converters'] = $converterData[$child['product_id']];
				}
			}
		}
		
		return $children;
	}

}