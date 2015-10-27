<?php

class GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ghstatements_satement_info_tab');
        $this->setDestElementId('template_edit_form');
        $this->setTitle(Mage::helper('ghstatements')->__('Manage Statements'));
    }


    protected function _beforeToHtml()
    {

        $this->addTab('statement_general_information', array(
            'label' => Mage::helper('ghstatements')->__('General Information'),
            'title' => Mage::helper('ghstatements')->__('General Information'),
            'content' => $this->getLayout()
                ->createBlock('ghstatements/adminhtml_vendor_statements_edit_tab_general')
                ->toHtml(),
        ));
        $this->addTab('statement_order_information', array(
            'label' => Mage::helper('ghstatements')->__('Order'),
            'title' => Mage::helper('ghstatements')->__('Order'),
            'content' => $this->getLayout()->createBlock('ghstatements/adminhtml_vendor_statements_edit_tab_order')
                ->toHtml(),
        ));
        $this->addTab('statement_refunds_information', array(
            'label' => Mage::helper('ghstatements')->__('Refunds'),
            'title' => Mage::helper('ghstatements')->__('Refunds'),
            'content' => $this->getLayout()->createBlock('ghstatements/adminhtml_vendor_statements_edit_tab_refunds')
                ->toHtml(),
        ));
        $this->addTab('statement_tracks_information', array(
            'label' => Mage::helper('ghstatements')->__('Tracking'),
            'title' => Mage::helper('ghstatements')->__('Tracking'),
            'content' => $this->getLayout()->createBlock('ghstatements/adminhtml_vendor_statements_edit_tab_track')
                ->toHtml(),
        ));
        $this->addTab('statement_tracks_rma', array(
            'label' => Mage::helper('ghstatements')->__('RMA'),
            'title' => Mage::helper('ghstatements')->__('RMA'),
            'content' => $this->getLayout()->createBlock('ghstatements/adminhtml_vendor_statements_edit_tab_rma')
                ->toHtml(),
        ));
        $this->addTab('statement_marketing_information', array(
            'label' => Mage::helper('ghstatements')->__('Marketing'),
            'title' => Mage::helper('ghstatements')->__('Marketing'),
            'content' => $this->getLayout()->createBlock('ghstatements/adminhtml_vendor_statements_edit_tab_marketing')
                ->toHtml(),
        ));
        $this->addTab('statement_payment_information', array(
            'label' => Mage::helper('ghstatements')->__('Payment'),
            'title' => Mage::helper('ghstatements')->__('Payment'),
            'content' => $this->getLayout()->createBlock('ghstatements/adminhtml_vendor_statements_edit_tab_payment')
                ->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}
