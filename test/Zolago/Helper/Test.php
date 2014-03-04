<?php
/**
 * helper for core functions
 */
class Zolago_Helper_Test {
    static public function getEntityType() {
        $model = Mage::getModel('eav/entity_type');
        $collection = $model->getCollection();
        $collection->setPageSize(1);
        $item = $collection->getFirstItem();
        $this->assertNotEmpty($item->getId());
        return $item;
    }
    static public function getAttributeSet($entity_type_id = null) {
        $model = Mage::getModel('eav/attribute_set');
        $collection = $model->getCollection();
        if ($entity_type_id) {
            $collection->addFieldToFilter('entity_type_id',$entity_type_id);                        
        }
        $collection->setPageSize(1);
        $item = $collection->getFirstItem();
        $this->assertNotEmpty($item->getId());
        return $item;
    }
    static public function createProduct() {
        $product  = Mage::getModel('catalog/product');
        
        $entity_type_id = self::getEntityType()->getId();
        $data = array (
            'entity_type_id' => $entity_type_id,
            'attribute_set_id' => self::getAttributeSet($entity_type_id),
            'type_id' => 'simple',
            'sku' => 'automatic_test_sku',                        
        );
        $product->setData($data);
        $product->save();
        $this->assertNotEmpty($product->getId());
        return $product;
    }
}