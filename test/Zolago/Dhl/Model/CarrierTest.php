<?php

class Zolago_Dhl_Model_CarrierTest extends ZolagoDb_TestCase {
    public function testCreate() {
        $model = Mage::getModel('zolagodhl/carrier');
        $this->assertNotEmpty($model);
        $this->assertFalse($model->isActive());
        $this->assertTrue($model->isTrackingAvailable());
        $this->assertInternalType('array',$model->getAllowedMethods());
        $this->assertNotEmpty($model->collectRates(new Mage_Shipping_Model_Rate_Request()));
    }
}
?>