<?php
/**
 * Zolago_Dropship_IndexController
 */
require_once Mage::getModuleDir('controllers', "Unirgy_Dropship") . DS . "IndexController.php";

class Zolago_Dropship_IndexController extends Unirgy_Dropship_IndexController {

    public function indexAction()
    {
        $this->_redirect('*/vendor');
    }
}