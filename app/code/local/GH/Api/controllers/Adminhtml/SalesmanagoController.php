<?php

/**
 * Class GH_Api_Adminhtml_SalesmanagoController
 */

class GH_Api_Adminhtml_SalesmanagoController extends Mage_Adminhtml_Controller_Action
{
    public function saveAction()
    {
        $vendorId = Mage::app()->getRequest()->getParam("vendor_id");

        $vendor = Mage::getModel("zolagodropship/vendor")->load($vendorId);
        $vendor->setData("modago_salesmanago_login", date("d.m.Y H:i:s"));
        $vendor->save();

    }
}