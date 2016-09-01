<?php

class Zolago_Catalog_Block_Vendor_Price_Modal extends Zolago_Catalog_Block_Vendor_Price_Abstract
{
	
	/**
	 * @return array
	 */
	public function getMsrpSourceOptions() {
		
		$product = $this->getProduct();
		
		$priceType = Mage::getSingleton('eav/config')->getAttribute(
			Mage_Catalog_Model_Product::ENTITY,
			Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_MSRP_TYPE_CODE
		);
		$priceType->setStoreId($this->getCurrentStoreId());
		
		$options = $priceType->getSource()->getAllOptions();
		
		/* Set price values - @todo? */
		foreach($options as &$option){
			if($option['value']==0){
				$value = null;
				if($product->isComposite()){
					$value = $this->getMinimalPrice("salePriceBefore");
				}else{
					$response = $this->_getProductPriceData(
						$this->_getVendor()->getExternalId(),
						$product->getSkuv(),
						$product->getId()
					);
					if($response){
						$value = $response['salePriceBefore'];
					}
				}
				
				$option['price'] = $value;
			}else{
				$option['price'] = $this->getProduct()->getMsrp();
			}
		}
		return $options;
	}
	
	/**
	 * @param int $vendorExtranlId
	 * @param string $vSku
	 * @return mixed
	 */
	protected function _getProductPriceData($vendorExtranlId, $vSku, $productId) {
		$key = "_" . $vendorExtranlId . "_" . $vSku;
		if(!$this->hasData($key)){

			$catalogProductResourceModel = Mage::getResourceModel('catalog/product');

			$reponse = null;

			try{
				$priceLabels = $this->getPriceLabels();
				foreach($priceLabels as $priceLabel){
					$externalPrice = $catalogProductResourceModel->getAttributeRawValue($productId, "external_price_$priceLabel", 0);
					if($externalPrice > 0){
						$reponse[$priceLabel] = $externalPrice;
					}
					unset($externalPrice);
				}

			}catch(Exception $e){

			}
			
			$this->setData($key, $reponse);
		}
		return $this->getData($key);
	}
	
	/**
	 * @return array
	 */
	public function getPriceSourceOptions() {
		
		
		$vendor = $this->_getVendor();
		$product = $this->getProduct();
		
		$priceType = Mage::getSingleton('eav/config')->getAttribute(
			Mage_Catalog_Model_Product::ENTITY,
			Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE
		);
		$priceType->setStoreId($this->getCurrentStoreId());
		
		$options = $priceType->getSource()->getAllOptions();

		$reponse = null;

		$catalogProductResourceModel = Mage::getResourceModel('catalog/product');

		// Check direct price for simple product
		if(!$product->isComposite()){
			$response = $this->_getProductPriceData(
				$vendor->getExternalId(),
				$product->getSkuv(),
				$product->getId()
			);

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
	 * 
	 * @todo fix to multiple prodcutc per option
	 */
	public function getMinimalPrices($index=null) {
		if(!$this->hasData("minimal_prices")){
			$prices = array();
			$ignorePrices = array();
			foreach($this->getChildren($this->getProduct()) as $attribute){
				foreach($attribute['children'] as $child){
					if(!isset($child['children'][0]['converter'])){
						$prices = array();
						// Some row have no prices - break all
						break 2;
					}

					foreach($child['children'][0]['converter'] as $type=>$price){
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
	 * 
	 * @todo fix for multiple configurable attributes
	 */
	public function getConverterPrice(array $child, $priceType) {
		if(isset($child['children'][0]['converter'][$priceType])){
			return $child['children'][0]['converter'][$priceType];
		}
		return null;
	}

	/**
	 * @return array
	 */
	public function getPriceLabels()
	{
		return array("A", "B", "C", "Z", "salePriceBefore");
	}
	
	/**
	 * @param array $children
	 * @param int $storeId
	 * @return array
	 */
	protected function _addConverterDataToChilds(array $children, $storeId) {

		$collection = Mage::getResourceModel("catalog/product_collection");
		/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
		$collection->addAttributeToSelect("skuv", "left");
		$collection->setStoreId($storeId);
		$collection->addIdFilter($this->_extractIdsFromAttributes($children));

		$converterData = array();

		$catalogProductResourceModel = Mage::getResourceModel('catalog/product');
		foreach($collection as $product){
			try{
				$priceLabels = $this->getPriceLabels();
				foreach($priceLabels as $priceLabel){
					$externalPrice = $catalogProductResourceModel->getAttributeRawValue($product->getId(), "external_price_$priceLabel", 0);
					if($externalPrice > 0){
						$converterData[$product->getId()][$priceLabel] = $externalPrice;
					}
					unset($externalPrice);
				}

			}catch(Exception $e){

			}
		}

		foreach($children as $attrKey=>$attribute){
			foreach($attribute['children'] as $childKey=>$child){
				foreach($child['children'] as $productRowKey=>$productRow){
					if(isset($converterData[$productRow['entity_id']])){
						$children[$attrKey]['children'][$childKey]['children'][$productRowKey]['converter'] = $converterData[$productRow['entity_id']];
					}
				}
			}
		}
		
		return $children;
	}
	
	/**
	 * 
	 * @param array $children
	 * @return array
	 */
	protected function _extractIdsFromAttributes($children) {
		$ids = array();
		foreach($children as $attrKey=>$attribute){
			foreach($attribute['children'] as $child){
				foreach($child['children'] as $productRow){
					$ids[]=$productRow['entity_id'];
				}
			}
		}
		return $ids;
	}

    /**
     * It is getting current campaign for product
     *
     * @param Zolago_Catalog_Model_Product $product
     * @return Zolago_Campaign_Model_Campaign|null
     */
    public function getCampaign(Zolago_Catalog_Model_Product $product) {

        /* @var $campaign Zolago_Campaign_Model_Campaign */
        $regular_id = $product->getData('campaign_regular_id');
        if (!empty($regular_id)) {
            return $campaign = Mage::getModel("zolagocampaign/campaign")->load($regular_id);
        }
        return null;
    }

}