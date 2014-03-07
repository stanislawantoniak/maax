<?php
class Zolago_Mapper_Model_IndexTest extends ZolagoDb_TestCase {
	
	public function testScenario() {
	    if (!no_coverage()) {
	        $this->markTestSkipped('Coverage');
	        return;
	    }
		
		$attributeSetName = "My test attribute set ";
		$attributeCode = "my_test";
		$attributeLabel = "My test";
		
		$newSet = Zolago_Helper_Test::addAttributeSet($attributeSetName);
		/* @var $newSet Mage_Eav_Model_Entity_Attribute_Set */
		
		// Test is object saved?
		$this->assertTrue((bool)$newSet->getId());
		
        $newAttribute = Zolago_Helper_Test::addNewAttribute($attributeCode,$attributeLabel);		
		// Test is object saved?
		$this->assertTrue((bool)$newAttribute->getId());
		
		
		/**
		 * 3: Add mapped attribute to set and group
		 */
		$catalogInstaller = Zolago_Helper_Test::getCatalogInstaller();
		$entityType = Zolago_Helper_Test::getEntityType();
		$catalogInstaller->addAttributeToSet($entityType->getId(), $newSet->getId(), 
				"General Information", $newAttribute->getId());
		
		/**
		 * 4: Add new category to map
		 */
		
		$category=$this->_getCategory();
		$category->save();
		$this->assertTrue((bool)$category->getId());
		
        $newMapper = Zolago_Helper_Test::addNewMapper($category,$newSet);
		$this->assertTrue((bool)$newMapper->getId());
		
		/**
		 * 6: Add new product with created set and positive matched attribute (P1)
		 */
		$productP1 = Zolago_Helper_Test::getNewProduct($newSet->getId(), $attributeCode, 1);

		/**
		 * 7: Add new product with created set and negative matched attribute (P2)
		 */	
		$productP2 = Zolago_Helper_Test::getNewProduct($newSet->getId(), $attributeCode, 0);

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
	
	
}