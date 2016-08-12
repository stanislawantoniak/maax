<?php

/**
 * logs from iai-shop
 */
class ZolagoOs_IAIShop_Adminhtml_Zosiaishop_LogController extends Mage_Adminhtml_Controller_Action
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
        return Mage::getSingleton('admin/session')->isAllowed('admin/vendors/vendor_integration_config/zosiaishop_log');
    }
}