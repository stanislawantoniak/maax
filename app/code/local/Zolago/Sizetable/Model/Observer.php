<?php
class Zolago_Sizetable_Model_Observer
{
	/**
     * Add tab to vendor view
     *
     * @param $observer Varien_Event_Observer
	 * 
     * @return Zolago_Sizetable_Model_Observer
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
                $block->addTab('sizetable', array(
                    'label'     => Mage::helper('zolagosizetable')->__('Size table settings'),
                    'class'     => 'ajax',
                    'url'       => $block->getUrl('sizetable/index/settings', array('_current' => true)),
                ));
        }
    }
}
