<?php
/**
 * Zolago_Dropship_IndexController
 */
require_once Mage::getModuleDir('controllers', "ZolagoOs_OmniChannel") . DS . "IndexController.php";

class Zolago_Dropship_IndexController extends ZolagoOs_OmniChannel_IndexController {

    public function indexAction()
    {
        $this->_redirect('*/vendor');
    }
}