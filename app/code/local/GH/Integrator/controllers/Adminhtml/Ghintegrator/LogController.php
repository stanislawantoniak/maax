<?php

class GH_Integrator_Adminhtml_Ghintegrator_LogController extends Mage_Adminhtml_Controller_Action
{
    /**
     * GH Integrator Log grid
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}