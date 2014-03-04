<?php
class Zolago_Mapper_Model_IndexTest extends ZolagoDb_TestCase {

    protected $_model;
    
    protected function _getModel() {
        if (!$this->_model) {
            $this->_model = Mage::getModel('zolagomapper/index');
            $this->assertNotEmpty($this->_model);
        }
        return $this->_model;
    }
    
    public function testCreate() {
        $model = $this->_getModel();
        $this->assertNotEmpty($model);
        
    }
	
	public function testScenaroio() {
		
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
		
		$catalogInstaller->addAttributeSet($entityType->getId(), $attributeSetName);
		$newSet = Mage::getModel("eav/attribute_set")->load($attributeSetName, "attribute_set_name");
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
			'sort_order'                 => 2,
			'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
			'group'                      => 'General Information',
			'label'						 => $attributeLabel,
		));
		$newAttribute = $eav->getAttribute($entityType->getId(), $attributeCode);
		/* @var $newAttribute Mage_Eav_Model_Entity_Attribute_Abstract */
		
		// Test is object saved?
		$this->assertTrue((bool)$newAttribute->getId());
		
		
		// 3: Add mapped attribute to set
		$catalogInstaller->addAttributeToSet($entityType->getId(), $newSet->getId(), "General");
		
		// 4: Add mapper with condiotions (A) and action to category (B)
		$newMapper = Mage::getModel("zolagomapper/mapper");
		$newMapper->setData(Zolago_Mapper_Helper_Test::getNewMapperData());
		$newMapper->save();
		
		// 5: Add new product with created set and positive matched attribute (P1)
		
		$productP1->setAttributeSetId($newSet->getId());
		// 6: Add new product with created set and negative matched attribute (P2)
		// 7: Save event - save item to queue
		// 8: Process queue
		// 9: Check is product (P1) in B category
		// 10: Check is product (P2) NOT in B category
	}
}