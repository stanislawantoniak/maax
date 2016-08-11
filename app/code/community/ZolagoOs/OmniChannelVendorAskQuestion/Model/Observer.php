<?php

class ZolagoOs_OmniChannelVendorAskQuestion_Model_Observer
{
    public function core_block_abstract_to_html_before($observer)
    {
        $block = $observer->getBlock();
        if (!$block instanceof Mage_Adminhtml_Block_Customer_Edit_Tabs
            || !Mage::app()->getRequest()->getParam('id', 0)
        ) {
            return;
        }

        if ($block instanceof Mage_Adminhtml_Block_Customer_Edit_Tabs) {
            if (Mage::getSingleton('admin/session')->isAllowed('sales/udropship/udqa')) {
                $block->addTab('udqa', array(
                    'label'     => Mage::helper('udqa')->__('Vendor Questions'),
                    'class'     => 'ajax',
                    'url'       => $block->getUrl('zosqaadmin/index/customerQuestions', array('_current' => true)),
                    'after'     => 'reviews'
                ));
            }
        }
    }
    public function udropship_adminhtml_vendor_tabs_after($observer)
    {
        $block = $observer->getBlock();
        if (!$block instanceof ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tabs
            || !Mage::app()->getRequest()->getParam('id', 0)
        ) {
            return;
        }

        if ($block instanceof ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tabs) {
            if (Mage::getSingleton('admin/session')->isAllowed('sales/udropship/udqa')) {
                $block->addTab('udqa', array(
                    'label'     => Mage::helper('udqa')->__('Customer Questions'),
                    'class'     => 'ajax',
                    'url'       => $block->getUrl('zosqaadmin/index/vendorQuestions', array('_current' => true)),
                    'after'     => 'products_section'
                ));
            }
        }
    }

    public function controller_action_layout_load_before($observer)
    {
        if ($observer->getAction()
            && $observer->getAction()->getFullActionName()=='catalog_product_view'
        ) {
            if (Mage::getStoreConfigFlag('udqa/general/product_info_tabbed')) {
                $observer->getAction()->getLayout()->getUpdate()->addHandle('udqa_catalog_product_view_tabbed');
            } else {
                $observer->getAction()->getLayout()->getUpdate()->addHandle('udqa_catalog_product_view');
            }
        }
    }

}