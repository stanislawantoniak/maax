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
        $model = Mage::helper('zolagosalesrule');
        $model->sendPromotionEmail(8,array(5,6,7,39,41));

        $this->loadLayout();
        $this->renderLayout();
    }
}
