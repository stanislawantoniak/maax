<?php

/**
 * Class Wf_Wf_IndexController
 */
class Wf_Wf_IndexController extends Mage_Core_Controller_Front_Action {

    /**
     * index
     */
    public function indexAction() {
        $this->_redirectUrl(Mage::getBaseUrl());

        $this->loadLayout();
        $this->renderLayout();
    }

}