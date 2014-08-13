<?php
class Zolago_Dhl_Model_UpsTest extends ZolagoDb_TestCase {
	

    public function testGetModelTrackAndTraceInfo() {
        $ret = Mage::getModel('orbashipping/carrier_client_ups');
        $ret->setAuth('psiwik','sivy53279A','DCD6476D0E64CD05');
        $out = $ret->getTrackAndTraceInfo();
        print_R($out);
        echo 'out';
    }	

}