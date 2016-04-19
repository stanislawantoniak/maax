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
        if (!$block instanceof ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tabs
            || !Mage::app()->getRequest()->getParam('id', 0)
        ) {
            return;
        }
        $ghDHLBlock = Mage::app()->getLayout()
            ->createBlock(
                'ghdhl/adminhtml_dropship_settings_dhl_grid',
                'admin.ghdhl.settings.dhl'
            )
            ->setVendorId(Mage::app()->getRequest()->getParam('id'))
            ->toHtml();

        if ($block instanceof ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tabs) {
            $block->addTab('ghdhl_section', array(
                'label' => Mage::helper('ghdhl')->__('DHL Account Access settings'),
                'content' => $ghDHLBlock
            ));
            $block->addTabToSection('ghdhl_section','logistic',30);
        }
    }

    public function udropship_adminhtml_vendor_edit_prepare_form($observer)
    {
        $block = $observer->getEvent()->getBlock();
        $form = $block->getForm();
        $fieldset = $form->getElement('vendor_form');
        $fieldset->addField('dhl_vendor', 'hidden', array(
            'name' => 'dhl_vendor',
        ));
    }
}
