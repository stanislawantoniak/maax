<?php
class GH_Api_Model_Observer
{
	/**
     * Add tab to vendor view
     *
     * @param $observer Varien_Event_Observer
	 * 
     * @return GH_Api_Model_Observer
     */
    public function udropship_adminhtml_vendor_tabs_after($observer)
    {
        $block = $observer->getEvent()->getBlock();
        $id = $observer->getEvent()->getId();
        $v = Mage::helper('udropship')->getVendor($id);

        if (!$block instanceof ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tabs
            || !Mage::app()->getRequest()->getParam('id', 0)
        ) {
            return;
        }

        if ($block instanceof ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tabs) {
            $ghApiBlock = Mage::app()
                ->getLayout()
                ->createBlock('ghapi/adminhtml_dropship_settings_ghapi_form', 'vendor.ghapi.form')
                ->setVendorId($v)
                ->toHtml();

                $block->addTab('ghapi_settings', array(
                    'label'     => Mage::helper('ghapi')->__('Integration settings'),
                    'after'     => 'sizetable_attributeset',
                    'content'	=> $ghApiBlock
                ));
                $block->addTabToSection('ghapi_settings','logistic',70);
        }
    }

}
