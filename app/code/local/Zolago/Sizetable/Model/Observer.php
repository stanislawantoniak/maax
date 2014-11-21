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
                $block->addTab('sizetable_brand', array(
                    'label'     => Mage::helper('zolagosizetable')->__('Size table brand settings'),
                    'content'	=> Mage::app()->getLayout()->createBlock('zolagosizetable/adminhtml_dropship_settings_grid_brand', 'admin.sizetable.settings.brand')
                        ->setVendorId(Mage::app()->getRequest()->getParam('id'))
                        ->toHtml(),
//                    'url' 		=> $block->getUrl('sizetable/index/brand', array('_current' => true)),
//                    'class'		=> 'ajax',
                ));
                $block->addTab('sizetable_attributeset', array(
                    'label'     => Mage::helper('zolagosizetable')->__('Size table attribute set settings'),
                    'content'	=> Mage::app()->getLayout()->createBlock('zolagosizetable/adminhtml_dropship_settings_grid_attributeset', 'admin.sizetable.settings.attributeset')
                        ->setVendorId(Mage::app()->getRequest()->getParam('id'))
                        ->toHtml(),
//                    'url' 		=> $block->getUrl('sizetable/index/attributeset', array('_current' => true)),
//                    'class'		=> 'ajax',
                ));
        }
    }
}
