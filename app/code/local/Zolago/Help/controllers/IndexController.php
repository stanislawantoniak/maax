<?php

class Zolago_Help_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Display the index help page
     */
    public function indexAction() {
        $this->loadLayout()->renderLayout();
//        echo 'test';

        /** @var Zolago_Payment_Model_Resource_Allocation $rm */
        $rm = Mage::getModel('zolagopayment/allocation');
        echo "<br/>";
        echo "<br/>";
        echo "<br/>";
        echo "<br/>";
        echo "<br/>";
        $rm->allocationTransaction(1, Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT);
    }

}
