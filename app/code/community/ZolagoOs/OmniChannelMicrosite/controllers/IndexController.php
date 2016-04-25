<?php
/**
  
 */
 
class ZolagoOs_OmniChannelMicrosite_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if ($vendor) {
            $this->_forward('default');
            return;
        }
        $this->_forward('index', 'index', 'cms');
    }
    public function defaultAction()
    {
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if ($vendor) {
            $this->getLayout()->helper('page/layout')
                ->applyHandle('two_columns_left');
            $this->loadLayout();
            $this->renderLayout();
            return;
        }
        $this->_forward('index', 'index', 'cms');
    }
}