<?php
/**
 * my coupons front controller
 */
require_once Mage::getConfig()->getModuleDir('controllers', "ZolagoOs_OmniChannelMicrosite") . DS . "IndexController.php";
    
class Zolago_SalesRule_IndexController extends ZolagoOs_OmniChannelMicrosite_IndexController {

    /**
     * index
     */
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
}
