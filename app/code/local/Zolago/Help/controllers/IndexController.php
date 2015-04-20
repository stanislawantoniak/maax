<?php

class Zolago_Help_IndexController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch() {
        parent::preDispatch();
        Mage::dispatchEvent('help_controller_index');
        return $this;
    }

    /**
     * Display the index help page
     */
    public function indexAction() {
        $this->loadLayout()->renderLayout();
    }

}
