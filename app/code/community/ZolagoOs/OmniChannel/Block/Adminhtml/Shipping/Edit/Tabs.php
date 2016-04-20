<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Shipping_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('shipping_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('udropship')->__('Manage Shipping'));
    }

    protected function _beforeToHtml()
    {
        $id = Mage::app()->getRequest()->getParam('id', 0);

        $this->addTab('form_section', array(
            'label'     => Mage::helper('udropship')->__('Shipping Information'),
            'title'     => Mage::helper('udropship')->__('Shipping Information'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_shipping_edit_tab_form')
                ->setShippingId($id)
                ->toHtml(),
        ));

        $this->addTab('methods_section', array(
            'label'     => Mage::helper('udropship')->__('Associated System Methods'),
            'title'     => Mage::helper('udropship')->__('Associated System Methods'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_shipping_edit_tab_methods')
                ->setShippingId($id)
                ->toHtml(),
        ));

        $this->addTab('titles_section', array(
            'label'     => Mage::helper('udropship')->__('Titles'),
            'title'     => Mage::helper('udropship')->__('Titles'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_shipping_edit_tab_titles')
                ->setShippingId($id)
                ->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}