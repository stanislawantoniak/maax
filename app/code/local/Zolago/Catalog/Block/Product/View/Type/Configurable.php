<?php

class Zolago_Catalog_Block_Product_View_Type_Configurable extends Mage_Catalog_Block_Product_View_Type_Configurable
{
    protected $_salableCount = 0;
    protected $_config = null;
    protected function _prepareConfig() {
		$return = Mage::helper("core")->jsonDecode(parent::getJsonConfig());
        $currentProduct = $this->getProduct();
        $strikeoutType = $currentProduct->getData('campaign_strikeout_price_type');
		
		$attributes = $return['attributes'];

		foreach($attributes as $keyAttr=>$attribute){
			if(is_array($attribute['options'])){
                //add info about product is salable
				foreach($attribute['options'] as $keyValue=>$value){
					if ($return['attributes'][$keyAttr]['options'][$keyValue]['is_salable'] = 
						$this->getIsOptionSalable($attribute['code'], $value['id'])) {
                        $this->_salableCount++;
                    }
				}
                //if product is in campaign (promo or sale) and strikeout price type is msrp
                //the old price (strikeout price) need to be msrp, don't need delta's
                if (Zolago_Campaign_Model_Campaign_Strikeout::STRIKEOUT_TYPE_MSRP_PRICE == $strikeoutType) {
                    foreach($attribute['options'] as $keyValue=>$value) {
                        $return['attributes'][$keyAttr]['options'][$keyValue]['oldPrice'] = "0";
                    }
                }
			}
		}

        //if product is in campaign (promo or sale) and strikeout price type is msrp
        //the old price (strikeout price) need to be msrp, don't need delta's
		$campaignId = (int)$currentProduct->getData("campaign_regular_id");
		$flag = $currentProduct->getData("product_flag");
        if (Zolago_Campaign_Model_Campaign_Strikeout::STRIKEOUT_TYPE_MSRP_PRICE == $strikeoutType
		|| (empty($campaignId) &&  $flag > 0)

		) {
            $return['oldPrice'] = '' . (float) $currentProduct->getStrikeoutPrice();
        }

        $this->_config = $return;
    }
    
    /**
     * number of salable sizes
     * @return int
     */
    public function getSalableCount() {
        if (is_null($this->_config)) {
            $this->_prepareConfig();
        }
        return $this->_salableCount;
    }
	/**
	 * 1) Add is salable flag to product option
	 * Flag is positive of any option-valued product is salable
     * 2) Update strikeout price if product in campaign (promo or sale)
	 * @return array
	 */
	public function getJsonConfig() {
        $config = $this->getConfig();
		return Mage::helper("core")->jsonEncode($config);
	}

    public function getConfig() {
        if (is_null($this->_config)) {
            $this->_prepareConfig();
        }
        return $this->_config;
    }
	
  /**
	 * @param string $attributeId
	 * @param int $attributeValue
	 */
	public function getIsOptionSalable($attributeCode, $attributeValue) {
		foreach($this->getAllowProducts() as $product){
			/* @var $product Mage_Catalog_Model_Product */
			if($product->getData($attributeCode)==$attributeValue){
				if($product->isSalable()){
					return true;
				}
			}
		}
		return false;
	}
	/**
	 * Add not se
	 * @return array
	 */
    public function getAllowProducts()
    {
		if(!$this->getIncludeNotSalable()){
			return parent::getAllowProducts();
		}
		
		if (!$this->hasAllowProducts()) {
            $products = array();
            $allProducts = $this->getProduct()->getTypeInstance(true)
                ->getUsedProducts(null, $this->getProduct());
            foreach ($allProducts as $product) {
				// Set all enabled products
				if($this->getProduct($product)->getStatus()!=Mage_Catalog_Model_Product_Status::STATUS_ENABLED){
					continue;
				}
				$products[] = $product;
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }
	
	/**
	 * Should include not salable products?
	 * @return boolean
	 */
	public function getIncludeNotSalable() {
		// Do not use it in admin
		if(Mage::app()->getStore()->isAdmin()){
			return false;
		}
		
		return (bool)(int)Mage::getStoreConfig("cataloginventory/options/include_not_salable");
	}

}
