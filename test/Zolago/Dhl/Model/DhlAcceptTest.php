<?php
class Zolago_Dhl_Model_DhlAcceptTest extends ZolagoDb_TestCase {
	
	public function testRma() {
//	    $helper = Mage::helper('zolagorma');
//	    $helper->rmaTracking();
        $model = Mage::getModel('udropship/observer');
        $model->cronCollectTracking();
	}
	public function __destruct() {
	    $this->_conn->commit();
    }	
}