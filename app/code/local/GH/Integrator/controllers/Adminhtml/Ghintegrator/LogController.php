<?php

/**
 * Class GH_Integrator_Adminhtml_Ghintegrator_LogController
 */
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

    /**
    * Acl check for this controller
    *
    * @return bool
    */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/vendors/vendor_integration_config/ghintegrator_log');
    }
}