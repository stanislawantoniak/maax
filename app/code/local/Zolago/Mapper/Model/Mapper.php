<?php
class Zolago_Mapper_Model_Mapper extends Mage_Rule_Model_Rule{
    
    protected function _construct() {
        $this->_init('zolagomapper/mapper');
    }
    
    protected $_productIds;

    public function getConditionsInstance() {
        return Mage::getModel('zolagomapper/mapper_condition_combine', $this);
    }

	
	public function getCategoryIdsAsString() {
		if(!$this->hasData('category_ids_as_string')){
			$this->setData('category_ids_as_string', implode(",", $this->getCategoryIds()));
		}
		return $this->getData("category_ids_as_string");
	}
	
	public function getCategoryIds() {
		if(!$this->hasData('category_ids')){
			$this->setData('category_ids', $this->getResource()->getCategoryIds($this)); //@todo implement
		}
		return $this->getData("category_ids");
	}

    public function getMatchingProductIds() {
		
		Varien_Profiler::start("ZolagoMapper::Matching");
        $this->_productIds = array();
        $this->setCollectedAttributes(array());
		
        $productCollection = Mage::getResourceModel('catalog/product_collection');
		/* @var $productCollection Mage_Catalog_Model_Resource_Product_Collection */
		$productCollection->addAttributeToFilter("attribute_set_id", $this->getAttributeSetId());
		$productCollection->addWebsiteFilter($this->getWebsiteId());

        $this->getConditions()->collectValidatedAttributes($productCollection);
        Mage::getSingleton('core/resource_iterator')->walk(
            $productCollection->getSelect(), array(array($this, 'callbackValidateProduct')), array(
                'attributes' => $this->getCollectedAttributes(),
                'product' => Mage::getModel('catalog/product'),
            )
        );
        unset($productCollection);
		Varien_Profiler::stop("ZolagoMapper::Matching");
        return $this->_productIds;
    }

    public function callbackValidateProduct($args) {
        $product = clone $args['product'];
        $product->setData($args['row']);
		$product->setStoreId($this->getDefaultStoreId());
        if ($this->getConditions()->validate($product)) {
            $this->_productIds[] = $product->getId();
        }
        unset($product);
        unset($args);
    }

	/**
	 * @return int
	 */
	public function getDefaultStoreId() {
		if(!$this->hasData("default_store_id")){
			$this->setData("default_store_id", Mage::app()->getWebsite($this->getWebsiteId())->getDefaultStore()->getId());
		}
		return $this->getData("default_store_id");
	}

	public function setDefaults(){
		$this->setIsActive(1);
	}
	
		
	public function getWebsiteIds() {
		return array($this->getWebsiteId());
	}
  
    
}

