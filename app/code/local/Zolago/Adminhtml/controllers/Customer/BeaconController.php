<?php

/**
 * Beacon admin controller in customer admin
 *
 * Class Zolago_Adminhtml_Customer_BeaconController
 */
class Zolago_Adminhtml_Customer_BeaconController extends Mage_Adminhtml_Controller_Action {

    /**
     * Show grid with Beacon data
     */
    public function dataAction() {
        /** @var Zolago_Adminhtml_Block_Customer_Beacon_Data_Grid $block */
        $block = $this->getLayout()->createBlock("zolagoadminhtml/customer_beacon_data_grid", "admin.customer.beacon.data.grid");
        $block->setCustomerId($this->getRequest()->getParam('id'))
            ->setUseAjax(true);
        $this->getResponse()->setBody($block->toHtml());
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
    }
}
