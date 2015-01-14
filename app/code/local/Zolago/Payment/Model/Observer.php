<?php

class Zolago_Payment_Model_Allocation_Observer {

    public function __construct() {

    }

    public function appendAllocation(Varien_Event_Observer $observer) {

        Mage::log("START appendAllocation in Zolago_Payment_Model_Allocation_Observer :");
        Mage::log($observer);
        Mage::log("END appendAllocation in Zolago_Payment_Model_Allocation_Observer :");
    }
}