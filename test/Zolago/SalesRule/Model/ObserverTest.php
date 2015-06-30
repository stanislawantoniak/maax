<?php
class Zolago_SalesRule_Model_Catalog_ObserverTest extends ZolagoDb_TestCase {
	
    public function testSendCouponMail() {
        Zolago_SalesRule_Model_Observer::sendSubscriberCouponMail();
    }	
    
}