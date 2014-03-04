<?php
/**
 * helper for mapper test
 */
class Zolago_Mapper_Helper_Test {
    static public class getNewMapperData() {        
        $data = array (
            'website_id' = 1,
            'attribute_set_id' => 1,
            'is_active' => 1,
            'name' => 'mapper testowy',
            'priority' => 0,
        );
    }
    static public class getNewMapper($data = null) {
        if (!$data) {
            $data = self::getNewMapperData();        
        }
        $model = Mage::getModel('zolagomapper');
        $model->setData($data);
        $model->save();
        $this->assertNotEmpty($model->getId());
        return $model;
    }
    
}