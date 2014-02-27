<?php
class Zolago_Mapper_Model_Mapper extends Mage_Rule_Model_Rule{
    
    protected function _construct() {
        $this->_init('zolagomapper/mapper');
    }
    
    protected $_conditions;
    protected $_productIds;
    protected $_product;

    public function getConditionsInstance() {
        return Mage::getModel('zolagomapper/mapper_condition_combine');
    }

    public function getConditions() {
        if (empty($this->_conditions)) {
            $this->_resetConditions();
        }
        return $this->_conditions;
    }
	
	public function getCategoryIds() {
		return implode(",", $this->getCategoryIdsAsArray());
	}
	
	public function getCategoryIdsAsArray() {
		if(!$this->hasData('category_ids_as_array')){
			$this->setData('category_ids_as_array', array()); //@todo implement
		}
		return $this->getData("category_ids_as_array");
	}

    protected function _afterLoad() {
        $conditions_arr = unserialize($this->getConditionsSerialized());
        if (!empty($conditions_arr) && is_array($conditions_arr)) {
            $this->getConditions()->loadArray($conditions_arr);
        }
        return parent::_afterLoad();
    }

    public function getMatchingProductIds() {
        $this->_productIds = array();
        $this->setCollectedAttributes(array());
        $productCollection = Mage::getResourceModel('catalog/product_collection');
        $this->getConditions()->collectValidatedAttributes($productCollection);
        Mage::getSingleton('core/resource_iterator')->walk(
            $productCollection->getSelect(), array(array($this, 'callbackValidateProduct')), array(
                'attributes' => $this->getCollectedAttributes(),
                'product' => Mage::getModel('catalog/product'),
            )
        );
        unset($productCollection);
        return $this->_productIds;
    }

    public function callbackValidateProduct($args) {
        $product = clone $args['product'];
        $product->setData($args['row']);
        if ($this->getConditions()->validate($product)) {
            $this->_productIds[] = $product->getId();
        }
        unset($product);
        unset($args);
    }

    public function run($load = false, $reindex = true) {
        ini_set('max_execution_time', 0);
        if ($this->getId()) {
            $matched_product_ids = $this->getMatchingProductIds();
           
            return true;
        }
        return false;
    }

  

    /**
     * @return Orba_Allegro_Model_Resource_Mapping_Collection
     */
    public function getCollection() {
       return parent::getCollection();
    }
    
}

