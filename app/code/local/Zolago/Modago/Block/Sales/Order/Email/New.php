<?php

class Zolago_Modago_Block_Sales_Order_Email_New extends Mage_Core_Block_Template {

    protected function _construct()
    {
        parent::_construct();
        Mage::getDesign()->setArea('frontend')->setPackageName('modago')->setTheme('default');
    }
}
