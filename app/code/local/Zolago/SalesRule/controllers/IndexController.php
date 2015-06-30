<?php
/**
 * my coupons front controller
 */
require_once Mage::getConfig()->getModuleDir('controllers', "Unirgy_DropshipMicrosite") . DS . "IndexController.php";
    
class Zolago_SalesRule_IndexController extends Unirgy_DropshipMicrosite_IndexController {

    /**
     * index
     */
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
}
