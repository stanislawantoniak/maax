<?php
class Modago_Integrator_Model_ConnectorTest extends ZolagoDb_TestCase {
	

    public function testPrice() {
        $model = Mage::getModel('modagointegrator/generator_price');
        $model->generate();        
    }	
    public function testDescription() {
        $model = Mage::getModel('modagointegrator/generator_description');
        $model->generate();        
    }	
    public function testStock() {
        $model = Mage::getModel('modagointegrator/generator_stock');
        $model->generate();                
    }	

}