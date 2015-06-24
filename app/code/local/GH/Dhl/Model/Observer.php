<?php

class GH_Dhl_Model_Observer
{
    /**
     * Add tab to vendor view
     *
     * @param $observer Varien_Event_Observer
     *
     * @return GH_Dhl_Model_Observer
     */
    public function udropship_adminhtml_vendor_tabs_after($observer)
    {
        $block = $observer->getBlock();
        if (!$block instanceof Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tabs
            || !Mage::app()->getRequest()->getParam('id', 0)
        ) {
            return;
        }

        if ($block instanceof Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tabs) {
            $block->addTab('ghdhl_dhl', array(
                'label' => Mage::helper('ghdhl')->__('DHL Account Access settings'),
                'content' => Mage::app()->getLayout()
                    ->createBlock(
                        'ghdhl/adminhtml_dropship_settings_dhl_grid',
                        'admin.ghdhl.settings.dhl'
                    )
                    ->setVendorId(Mage::app()->getRequest()->getParam('id'))
                    ->toHtml()
            ));
        }
    }
}
