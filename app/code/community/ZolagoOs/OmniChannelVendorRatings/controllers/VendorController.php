<?php

class ZolagoOs_OmniChannelVendorRatings_VendorController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $_vendor = Mage::helper('udropship')->getVendor($this->getRequest()->getParam('id'));
        if (!$_vendor->getId()) {
            $this->_redirectReferer();
            return $this;
        }
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Reviews for vendor %s', $_vendor->getVendorname()));
        $this->renderLayout();
    }
    public function reviewListJsonAction()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('udratings_vendor_review_list');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        $this->getResponse()->setBody($output);
    }

}
