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
    public function testAttributeList() {
        // create mapper
        
    }

}