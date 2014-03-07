<?php
class Zolago_Catalog_Model_Category_FilterTest extends ZolagoDb_TestCase {
    public function testCreate() {
        $model = Mage::getModel('zolagocatalog/category_filter');
        $this->assertNotNull($model);
    }
    protected function _getAttribute() {
        $resource =  Mage::getSingleton('core/resource');
        $table = $resource->getTableName('eav/attribute');
        $query = 'SELECT attribute_id FROM '.$table.' WHERE attribute_code = \'description\'';  
        $data = $resource->getConnection('core_read')->fetchAll($query);
        $this->assertNotEmpty(count($data));
        return array_pop($data);
    }
    protected function _getModel() {
        if (!$this->_model) {
            $model = Mage::getModel('zolagocatalog/category_filter');
            $attribute = $this->_getAttribute();
            $attribute_id = $attribute['attribute_id'];
            $category = Zolago_Helper_Test::getItem('catalog/category');
            $this->assertNotEmpty($category);
            $this->assertNotEmpty($attribute_id);
            $data = array (
                        'attribute_id' => $attribute_id,
                        'category_id' => $category->getId(),
                    );
            $model->setData($data);
            $model->save();
            $this->assertNotEmpty($model->getId());
            $this->_model = $model;
        }
        return $this->_model;
    }
    public function testSave() {
        $model = $this->_getModel();
        $newAttribute = $model->getAttribute();
        $attribureId = $model->getAttributeId(); 
        $this->assertNotEmpty($newAttribute,$attributeId);
    }
    public function testCollection() {
        $model = $this->_getModel();
        $categoryId = $model->getCategoryId();
        $this->assertNotEmpty($categoryId);
        $category = Mage::getModel('catalog/category');
        $category->load($categoryId);
        $this->assertNotEmpty($category->getId());
        $collection = $model->getCollection();
        $this->assertNotEmpty($collection);
        $collection->addCategoryFilter($category);
        $this->assertGreaterThan(0,count($collection));        
    }
    protected function _createCategory($parentId) {
        $newCategory = Mage::getModel('catalog/category');
        $data = Zolago_Helper_Test::getCategoryData();
        $data['parent_id'] = $cid;
        $newCategory->setData($data);
        $newCategory->save();
        $this->assertNotEmpty($newCategory->getId());
        return $newCategory;        
    }
    public function testAttributeListSameSet() {
        if (!no_coverage()) {
            $this->markTestSkipped('Coverage');
            return;
        }
        // create child categories
        $category = Zolago_Helper_Test::getItem('catalog/category');
        $this->assertNotEmpty($category);
        $cid = $category->getId();
        $this->assertNotEmpty($cid);

        $cid_1 = $this->_createCategory($cid);
        $cid_2 = $this->_createCategory($cid);
        
        // create attribute set
        $newSet = Zolago_Helper_Test::addAttributeSet('zestaw testowy');
        $this->assertNotEmpty($newSet->getId());
        
        // add attributes
        $attr_1 = Zolago_Helper_Test::addNewAttribute('code_1','label_1');
        $this->assertNotEmpty($attr_1->getId());
        $attr_2 = Zolago_Helper_Test::addNewAttribute('code_2','label_2');
        $this->assertNotEmpty($attr_2->getId());
        
        // connect attributes to set
		$catalogInstaller = Zolago_Helper_Test::getCatalogInstaller();
		$entityType = Zolago_Helper_Test::getEntityType();
		$catalogInstaller->addAttributeToSet($entityType->getId(), $newSet->getId(), 
				"General Information", $attr_1->getId());
		$catalogInstaller->addAttributeToSet($entityType->getId(), $newSet->getId(), 
				"General Information", $attr_2->getId());
        // add mappers
        $mapper_1 = Zolago_Helper_Test::addNewMapper($cid_1,$newSet,serialize(Zolago_Helper_Test::getConditionsArray('code_1')));
        $this->assertNotEmpty($mapper_1->getId());
        $mapper_2 = Zolago_Helper_Test::addNewMapper($cid_2,$newSet,serialize(Zolago_Helper_Test::getConditionsArray('code_2')));
        $this->assertNotEmpty($mapper_2->getId());
        
        // test!
        $model = Mage::getResourceModel('zolagomapper/mapper');
        $this->assertNotEmpty($model);
        $ret = $model->getAttributesByCategory($cid_1->getId());
        $expected = array (
            $attr_1->getId() => 'label_1',
            $attr_2->getId() => 'label_2',
        );
        $this->assertEquals($expected,$ret);
        $ret = $model->getAttributesByCategory($cid_2->getId());
        $this->assertEquals($expected,$ret);        
    }
    public function testAttributeListAnotherSet() {
        if (!no_coverage()) {
            $this->markTestSkipped('Coverage');
            return;
        }
        // create child categories
        $category = Zolago_Helper_Test::getItem('catalog/category');
        $this->assertNotEmpty($category);
        $cid = $category->getId();
        $this->assertNotEmpty($cid);

        $cid_1 = $this->_createCategory($cid);
        $cid_2 = $this->_createCategory($cid);
        
        // create attribute sets
        $newSet1 = Zolago_Helper_Test::addAttributeSet('zestaw testowy 1');
        $this->assertNotEmpty($newSet1->getId());
        $newSet2 = Zolago_Helper_Test::addAttributeSet('zestaw testowy 2');
        $this->assertNotEmpty($newSet2->getId());
        
        // add attributes
        $attr_1 = Zolago_Helper_Test::addNewAttribute('code_b_1','label_3');
        $this->assertNotEmpty($attr_1->getId());
        $attr_2 = Zolago_Helper_Test::addNewAttribute('code_b_2','label_4');
        $this->assertNotEmpty($attr_2->getId());
        
        // connect attributes to set
		$catalogInstaller = Zolago_Helper_Test::getCatalogInstaller();
		$entityType = Zolago_Helper_Test::getEntityType();
		$catalogInstaller->addAttributeToSet($entityType->getId(), $newSet1->getId(), 
				"General Information", $attr_1->getId());
		$catalogInstaller->addAttributeToSet($entityType->getId(), $newSet2->getId(), 
				"General Information", $attr_2->getId());
        // add mappers
        $mapper = Zolago_Helper_Test::getNewMapper('nowy mapper 1');
        $mapper_1 = Zolago_Helper_Test::addNewMapper($cid_1,$newSet1,serialize(Zolago_Helper_Test::getConditionsArray('code_b_1')),$mapper);
        $this->assertNotEmpty($mapper_1->getId());
        $mapper = Zolago_Helper_Test::getNewMapper('nowy mapper 2');
        $mapper_2 = Zolago_Helper_Test::addNewMapper($cid_2,$newSet2,serialize(Zolago_Helper_Test::getConditionsArray('code_b_2')),$mapper);
        $this->assertNotEmpty($mapper_2->getId());
        
        // test!
        $model = Mage::getResourceModel('zolagomapper/mapper');
        $this->assertNotEmpty($model);
        $ret = $model->getAttributesByCategory($cid_1->getId());
        $expected = array (
            $attr_1->getId() => 'label_3',
        );
        $this->assertEquals($expected,$ret);
        $expected = array (
            $attr_2->getId() => 'label_4',
        );
        $ret = $model->getAttributesByCategory($cid_2->getId());
        $this->assertEquals($expected,$ret);        
    }
    
}