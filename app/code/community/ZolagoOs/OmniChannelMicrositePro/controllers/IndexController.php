<?php
/**
  
 */

class ZolagoOs_OmniChannelMicrositePro_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if ($vendor) {
            $this->_forward('landingPage');
            return;
        }
        $this->_forward('index', 'index', 'cms');
    }
    public function landingPageAction()
    {
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if ($vendor) {
            if (!Mage::helper('cms/page')->renderPage($this, $vendor->getVendorLandingPage())) {
                $this->_forward('default', 'index', 'umicrosite');
            }
            return;
        }
        $this->_forward('index', 'index', 'cms');
    }
}