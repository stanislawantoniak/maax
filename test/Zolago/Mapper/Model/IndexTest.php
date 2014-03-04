<?php
class Zolago_Mapper_Model_IndexTest extends Zolago_TestCase {
	
	public function testScenario() {
		
		$attributeSetName = "My test attribute set ";
		$attributeCode = "my_test";
		$attributeLabel = "My test";
		
		$eav = Mage::getSingleton("eav/config");
		/* @var $eav Mage_Eav_Model_Config */
		$catalogInstaller = Mage::getResourceModel("catalog/setup", "core_setup");
		/* @var $catalogInstaller Mage_Catalog_Model_Resource_Setup */
		
		/**
		 * 1: Add new attribute set
		 */
        $entityType = $eav->getEntityType(Mage_Catalog_Model_Product::ENTITY);
		/* @var $entityType Mage_Eav_Model_Entity_Type */
		
        $newSet = Mage::getModel("eav/entity_attribute_set");
            /* @var $newSet Mage_Eav_Model_Entity_Attribute_Set */
            
		$newSet->setAttributeSetName($attributeSetName);
		$newSet->setEntityTypeId($entityType->getId());
		$newSet->save();
		$newSet->setAttributeSetName($newSet->getAttributeSetName() . " " . $newSet->getId());
		$newSet->initFromSkeleton($entityType->getDefaultAttributeSetId());
		$newSet->save();
			
		/* @var $newSet Mage_Eav_Model_Entity_Attribute_Set */
		
		// Test is object saved?
		$this->assertTrue((bool)$newSet->getId());
		
		/**
		 * 2: Add new mapped attribute
		 */
		$catalogInstaller->addAttribute($entityType->getId(), $attributeCode, array(
			'type'                       => 'int',
			'input'                      => 'select',
			'source'                     => 'eav/entity_attribute_source_boolean',
			'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
			'label'						 => $attributeLabel
		));
		$newAttribute = $eav->getAttribute($entityType->getId(), $attributeCode);
		$newAttribute->setIsMappable(1)->save();
		/* @var $newAttribute Mage_Eav_Model_Entity_Attribute_Abstract */
		
		// Test is object saved?
		$this->assertTrue((bool)$newAttribute->getId());
		
		
		/**
		 * 3: Add mapped attribute to set and group
		 */
		$catalogInstaller->addAttributeToSet($entityType->getId(), $newSet->getId(), 
				"General Information", $newAttribute->getId());
		
		/**
		 * 4: Add new category to map
		 */
		
		$category=$this->_getCategory();
		$category->save();
		$this->assertTrue((bool)$category->getId());
		
		/**
		 * 5: Add mapper with condiotions (A) and action to category (B)
		 */
		$newMapper = $this->_getMapper();
		$newMapper->setCategoryIds(array($category->getId()));
		$newMapper->setWebsiteId($this->_getStore()->getWebsiteId());
		$newMapper->setAttributeSetId($newSet->getId());
		$newMapper->setData('conditions_serialized', $this->_getConditions());
		$newMapper->save();
		$this->assertTrue((bool)$newMapper->getId());

		/**
		 * 6: Add new product with created set and positive matched attribute (P1)
		 */
		$productP1 = $this->_getTestedProduct($newSet->getId(), $attributeCode, 1);

		/**
		 * 7: Add new product with created set and negative matched attribute (P2)
		 */	
		$productP2 = $this->_getTestedProduct($newSet->getId(), $attributeCode, 0);

		/**
		 * 8: Save event - save item to queue
		 */
		$productP1->save();
		$this->assertTrue((bool)$productP1->getId());
		$productP2->save();
		$this->assertTrue((bool)$productP2->getId());
		
		/**
		 * 9: Process queue
		 */
		$queue = Mage::getModel('zolagomapper/queue_product');
		/* @var $queue Zolago_Mapper_Model_Queue_Product */
		$queue->process();
		
		/**
		 * 10: Check is product (P1) in B category
		 */
		$productP1 = Mage::getModel('catalog/product')->load($productP1->getId());	
		$this->assertEquals($productP1->getData($attributeCode), 1);
		$this->assertTrue(in_array($category->getId(), $productP1->getCategoryIds()));

		/**
		 * 11: Check is product (P2) NOT in B category
		 */
		$productP2 = Mage::getModel('catalog/product')->load($productP2->getId());
		$this->assertEquals($productP2->getData($attributeCode), 0);
		$this->assertFalse(in_array($category->getId(), $productP2->getCategoryIds()));
	}
	
	/**
	 * @param type $attributeSetId
	 * @param type $testCode
	 * @param type $testValue
	 * @return Mage_Catalog_Model_Product
	 */
	protected function _getTestedProduct($attributeSetId, $testCode, $testValue) {
		$product = Mage::getModel("catalog/product");
		$product->setAttributeSetId($attributeSetId);
		$product->setData($testCode, $testValue);
		$product->setSku('some-sku-' . (int)$testValue);
		$product->setTypeId('simple');
		$product->setName('Some cool product name ' . (int)$testValue);
		$product->setWebsiteIds(array($this->_getStore()->getWebsite()->getId()));
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
	
	/**
	 * @return Mage_Core_Model_Store
	 */
	protected function _getStore() {
		return Mage::app()->getStore(true);
	}
	
	/**
	 * @return Mage_Catalog_Model_Category
	 */
	protected function _getCategory(){
		$category = Mage::getModel("catalog/category");
		/* @var $category Mage_Catalog_Model_Category */
		$rootCategory = Mage::getModel("catalog/category")->load(
				$this->_getStore()->getRootCategoryId()
		);
		$category
			->setName('My category')
			->setPath($rootCategory->getPath())
			->setIsActive(1)
			->setIsAnchor(1);
		
		return $category;
	}
	
	/**
	 * @return Zolago_Mapper_Model_Mapper
	 */
	protected function _getMapper() {
		$newMapper = Mage::getModel("zolagomapper/mapper");
		$newMapper->setData(array(
            'is_active' => 1,
            'name' => 'Mapper testowy',
            'priority' => 0,
			'category_ids' => array(),
		));
		return $newMapper;
	}
	
	protected function _getConditions() {
		return 'a:7:{s:4:"type";s:22:"rule/condition_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";s:10:"conditions";a:1:{i:0;a:5:{s:4:"type";s:37:"zolagomapper/mapper_condition_product";s:9:"attribute";s:7:"my_test";s:8:"operator";s:2:"==";s:5:"value";s:1:"1";s:18:"is_value_processed";b:0;}}}';
	}
}