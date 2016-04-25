<?php

class ZolagoOs_OmniChannelTierShipping_Model_Observer
{
    public function udprod_product_edit_element_types($observer)
    {
        $response = $observer->getResponse();
        $types = $response->getTypes();
        $types['udtiership_rates'] = Mage::getConfig()->getBlockClassName('udtiership/vendor_product_form_rates');
        $types['text_udtiership_rates'] = Mage::getConfig()->getBlockClassName('udtiership/vendor_product_form_rates');
        $response->setTypes($types);
    }
    public function udropship_adminhtml_vendor_tabs_after($observer)
    {
        $tsHlp = Mage::helper('udtiership');
        $block = $observer->getBlock();
        if (!$tsHlp->isV2Rates()) {
            $block->addTab('udtiership', array(
                'label'     => Mage::helper('udtiership')->__('Shipping Rates'),
                'after'     => 'shipping_section',
                'content'   => Mage::app()->getLayout()->createBlock('udtiership/adminhtml_vendorEditTab_shippingRates_form', 'vendor.tiership.form')
                    ->toHtml()
            ));
        } else {
            $block->addTab('udtiership', array(
                'label'     => Mage::helper('udtiership')->__('Shipping Rates'),
                'after'     => 'shipping_section',
                'content'   => Mage::app()->getLayout()->createBlock('udtiership/adminhtml_vendorEditTab_shippingRates_v2_form', 'vendor.tiership.form')
                    ->toHtml()
            ));
        }
    }
    public function udropship_vendor_load_after($observer)
    {
        Mage::helper('udtiership')->processTiershipRates($observer->getVendor());
        Mage::helper('udtiership')->processTiershipSimpleRates($observer->getVendor());
    }
    public function udropship_vendor_save_after($observer)
    {
        Mage::helper('udtiership')->processTiershipRates($observer->getVendor());
        Mage::helper('udtiership')->processTiershipSimpleRates($observer->getVendor());
        Mage::helper('udtiership')->saveVendorV2Rates($observer->getVendor());
        Mage::helper('udtiership')->saveVendorV2SimpleRates($observer->getVendor());
        Mage::helper('udtiership')->saveVendorV2SimpleCondRates($observer->getVendor());
    }
    public function udropship_vendor_save_before($observer)
    {
        Mage::helper('udtiership')->processTiershipRates($observer->getVendor(), true);
        Mage::helper('udtiership')->processTiershipSimpleRates($observer->getVendor(), true);
    }

    public function controller_front_init_before($observer)
    {
        $this->_initConfigRewrites();
    }

    public function udropship_init_config_rewrites()
    {
        $this->_initConfigRewrites();
    }
    protected function _initConfigRewrites()
    {
        if (!Mage::helper('udtiership')->isV2Rates()) return;
        Mage::getConfig()->setNode('global/models/udtiership/rewrite/carrier', 'ZolagoOs_OmniChannelTierShipping_Model_V2_Carrier');
    }

}