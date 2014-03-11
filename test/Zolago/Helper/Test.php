<?php
/**
 * helper for core functions
 */
class Zolago_Helper_Test {
    
    /**
     * function returns fist object from database
     * @param string $modelName 
     */
    static public function getItem($modelName) { 
        $model = Mage::getModel($modelName);
        $collection = $model->getCollection();        
        $collection->setPageSize(1);
        $item = $collection->getFirstItem();
        return $item;
        
    }
    
    /**
     * return sample category data
     */
    static public function getCategoryData() {
        $data = array (
            'name' => 'category',
            'is_active' => true,
            'parent_id' => 0,
            'entity_type_id' => 3,
       );
       return $data; 
    }
    public function getEav() {
        return Mage::getSingleton("eav/config");
    }
    public static function getEntityType() {
		$eav = self::getEav();
		/* @var $eav Mage_Eav_Model_Config */
        $entityType = $eav->getEntityType(Mage_Catalog_Model_Product::ENTITY);
		/* @var $entityType Mage_Eav_Model_Entity_Type */
		return $entityType;
    }
    public static function getCatalogInstaller() {
		$catalogInstaller = Mage::getResourceModel("catalog/setup", "core_setup");
		/* @var $catalogInstaller Mage_Catalog_Model_Resource_Setup */
		return $catalogInstaller;
    }    
    /**
     * Add new attribute set
     * @param string $attributeSetName name of set
     */
    static public function addAttributeSet($attributeSetName) {
		/**
		 * 1: Add new attribute set
		 */
		$entityType = self::getEntityType();
        $newSet = Mage::getModel("eav/entity_attribute_set");
            /* @var $newSet Mage_Eav_Model_Entity_Attribute_Set */
            
		$newSet->setAttributeSetName($attributeSetName);
		$newSet->setEntityTypeId($entityType->getId());
		$newSet->save();
		$newSet->setAttributeSetName($newSet->getAttributeSetName() . " " . $newSet->getId());
		$newSet->initFromSkeleton($entityType->getDefaultAttributeSetId());
		$newSet->save();
		return $newSet;
    }
    
    /**
     * add new mapped attribute
     */
    static public function addNewAttribute($attributeCode,$attributeLabel) {
        $catalogInstaller = self::getCatalogInstaller();
        $entityType = self::getEntityType();
        $eav = self::getEav();
		/**
		 * 2: Add new mapped attribute
		 */
		$catalogInstaller->addAttribute($entityType->getId(), $attributeCode, array(
			'type'                       => 'int',
			'input'                      => 'select',
			'source'                     => 'eav/entity_attribute_source_boolean',
			'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
			'label'						 => $attributeLabel,
			'user_defined'               => 1,
		));
		$newAttribute = $eav->getAttribute($entityType->getId(), $attributeCode);
		$newAttribute->setIsFilterable(1);
		$newAttribute->setIsMappable(1)->save();
		/* @var $newAttribute Mage_Eav_Model_Entity_Attribute_Abstract */
		return $newAttribute;
    }
    
    /**
     * add new category mapper
     */
    static public function getNewMapper($name) {
		$newMapper = Mage::getModel("zolagomapper/mapper");
		$newMapper->setData(array(
            'is_active' => 1,
            'name' => $name,
            'priority' => 0,
			'category_ids' => array(),
		));
		return $newMapper;
    }
	public static function getConditions() {
	    $cond = self::getConditionsArray();
		return serialize($cond);
		//'a:7:{s:4:"type";s:22:"rule/condition_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";s:10:"conditions";a:1:{i:0;a:5:{s:4:"type";s:37:"zolagomapper/mapper_condition_product";s:9:"attribute";s:7:"my_test";s:8:"operator";s:2:"==";s:5:"value";s:1:"1";s:18:"is_value_processed";b:0;}}}';
	}
	public static function getConditionsArray($attributeName = 'my_test') {
	    $out = array (
	        'type' => 'rule/condition_combine',
	        'attribute' => null,
	        'operator' => null,
	        'value' => 1,
	        'is_value_processed' => null,
	        'aggregator' => 'all',
	        'conditions' => array (
	            array (
	                'type' => 'zolagomapper/mapper_condition_product',
	                'attribute' => $attributeName,
	                'operator' => '==',
	                'value' => 1,
	                'is_value_processed' => null,
                ),
            ),
        );
	    return $out;          
	        
	}
	public static function addNewMapper($category,$newSet,$conditionsSerialized = null,$newMapper = null) {
		/**
		 * 5: Add mapper with condiotions (A) and action to category (B)
		 */
		$newMapper = $newMapper? $newMapper:Zolago_Helper_Test::getNewMapper();
		$newMapper->setCategoryIds(array($category->getId()));
		$newMapper->setWebsiteId(Mage::app()->getStore(true)->getWebsiteId());
		$newMapper->setAttributeSetId($newSet->getId());
		$newMapper->setData('conditions_serialized', $conditionsSerialized? $conditionsSerialized: Zolago_Helper_Test::getConditions());
		$newMapper->save();
		return $newMapper;
	}
	/**
	 * @param type $attributeSetId
	 * @param type $testCode
	 * @param type $testValue
	 * @return Mage_Catalog_Model_Product
	 */
	static public function getNewProduct($attributeSetId, $testCode, $testValue) {
		$product = Mage::getModel("catalog/product");
		$product->setAttributeSetId($attributeSetId);
		$product->setData($testCode, $testValue);
		$product->setSku('some-sku-' . (int)$testValue);
		$product->setTypeId('simple');
		$product->setName('Some cool product name ' . (int)$testValue);
		$product->setWebsiteIds(array(Mage::app()->getStore()->getWebsite()->getId()));
		$product->setDescription('Full description here');
		$product->setShortDescription('Short description here');
		$product->setPrice(39.99); # Set some price
		
		//Default Magento attribute
		$product->setWeight(4.0000);
		$product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
		$product->setStatus(1);
		$product->setCreatedAt(strtotime('now'));
		return $product;
	}

}