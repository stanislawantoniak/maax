<?php
/**
 * Zolago_Dropship_IndexController
 */
require_once 'Unirgy/Dropship/controllers/IndexController.php';

class Zolago_Dropship_IndexController extends Unirgy_Dropship_IndexController {

    public function indexAction()
    {
        $this->_forward('index', 'vendor', "udropship");
    }
}