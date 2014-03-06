<?php
/**
 * helper for core functions
 */
class Zolago_Helper_Test {
    static public function getItem($modelName) { 
        $model = Mage::getModel('eav/entity_type');
        $collection = $model->getCollection();
        $collection->setPageSize(1);
        $item = $collection->getFirstItem();
        return $item;
        
    }
}