<?php
/**
 * helper for mapper test
 */
class Zolago_Mapper_Helper_Test {
     public static function getNewMapperData() {        
        $data = array (
            'website_id' => 1,
            'attribute_set_id' => 1,
            'is_active' => 1,
            'name' => 'Mapper testowy',
            'priority' => 0,
			'category_ids' => array(),
			'conitions_serialized' => 'a:7:{s:4:"type";s:22:"rule/condition_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";s:10:"conditions";a:1:{i:0;a:5:{s:4:"type";s:37:"zolagomapper/mapper_condition_product";s:9:"attribute";s:5:"price";s:8:"operator";s:1:">";s:5:"value";d:200;s:18:"is_value_processed";b:0;}}}'
        );
		return $data;
    }
    public static function getNewMapper($data = null) {
        if (!$data) {
            $data = self::getNewMapperData();        
        }
        $model = Mage::getModel('zolagomapper/mapper');
        $model->setData($data);
        $model->save();
        $this->assertNotEmpty($model->getId());
        return $model;
    }
    
}