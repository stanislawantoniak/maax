<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('vendor_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('udropship')->__('Manage Vendors'));
    }

    protected function _beforeToHtml()
    {
        $id = Mage::app()->getRequest()->getParam('id', 0);

        $this->addTab('form_section', array(
            'label'     => Mage::helper('udropship')->__('Vendor Information'),
            'title'     => Mage::helper('udropship')->__('Vendor Information'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_form')
                ->setVendorId($id)
                ->toHtml(),
        ));

        $this->addTab('preferences_section', array(
            'label'     => Mage::helper('udropship')->__('Preferences'),
            'title'     => Mage::helper('udropship')->__('Preferences'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_preferences', 'vendor.preferences.form')
                ->setVendorId($id)
                ->toHtml(),
        ));

        $this->addTab('custom_section', array(
            'label'     => Mage::helper('udropship')->__('Custom Data'),
            'title'     => Mage::helper('udropship')->__('Custom Data'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_custom', 'vendor.custom.form')
                ->setVendorId($id)
                ->toHtml(),
        ));

        $this->addTab('shipping_section', array(
            'label'     => Mage::helper('udropship')->__('Shipping methods'),
            'title'     => Mage::helper('udropship')->__('Shipping methods'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_shipping', 'vendor.shipping.grid')
                ->setVendorId($id)
                ->toHtml(),
        ));

        if ($id) {
            $this->addTab('products_section', array(
                'label'     => Mage::helper('udropship')->__('Associated Products'),
                'title'     => Mage::helper('udropship')->__('Associated Products'),
                'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_products', 'vendor.product.grid')
                    ->setVendorId($id)
                    ->toHtml(),
            ));
        }

        if(($tabId = $this->getRequest()->getParam('tab'))) {
            $this->setActiveTab($tabId);
        }

        Mage::dispatchEvent('udropship_adminhtml_vendor_tabs_after', array('block'=>$this, 'id'=>$id));

        return parent::_beforeToHtml();
    }
}