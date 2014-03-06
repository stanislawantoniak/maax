<?php
class Zolago_Catalog_Model_System_Layer_Filter_SourceTest extends ZolagoDb_TestCase {
    public function testCreate() {
        $model = Mage::getModel('zolagocatalog/system_layer_filter_source');
        $this->assertNotEmpty($model);
    }
}
