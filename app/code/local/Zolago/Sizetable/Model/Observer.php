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
        $block->addTab('sizetable_brand', array(
            'label'     => Mage::helper('zolagosizetable')->__('Size table settings'),
            'content'   => Mage::app()->getLayout()->createBlock('zolagosizetable/adminhtml_dropship_edit_tab_settings', 'vendor.sizetablesettings.form')
                ->toHtml()
        ));
    }
	
}
