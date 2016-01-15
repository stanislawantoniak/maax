<?php

/**
 * Class Zolago_Adminhtml_SalesmanagoController
 */
class Zolago_Adminhtml_SalesmanagoController extends Mage_Adminhtml_Controller_Action
{
    public function saveAction()
    {
        $vendorId = Mage::app()->getRequest()->getParam("vendor_id");

        $currentTimestamp = Mage::getModel('core/date')->timestamp(time()); //Magento's timestamp function makes a usage of timezone and converts it to timestamp
        $date = date('d.m.Y H:i:s', $currentTimestamp);

        $vendor = Mage::getModel("zolagodropship/vendor")->load($vendorId);
        $vendor->setData("modago_salesmanago_login", $date);
        $vendor->save();

    }
}