<?php

class GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tab_Refunds
    extends GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tab_Statement
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('statement_refund');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ghstatements/refund')
            ->getCollection()
            ->addFieldToFilter('statement_id', $this->getStatement()->getId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('ghstatements')->__('ID'),
            'sortable' => true,
            'width' => '60',
            'index' => 'id'
        ));
        $this->addColumn('po_increment_id', array(
            'header' => Mage::helper('ghstatements')->__('Order number'),
            'sortable' => true,
            'width' => '60',
            'index' => 'po_increment_id'
        ));
        $this->addColumn('rma_increment_id', array(
            'header' => Mage::helper('ghstatements')->__('RMA number'),
            'sortable' => true,
            'width' => '60',
            'index' => 'rma_increment_id'
        ));
        $this->addColumn('date', array(
            'header' => Mage::helper('ghstatements')->__('Date'),
            'sortable' => true,
            'width' => '60',
            'index' => 'date',
            'type' => 'date',
        ));
        $this->addColumn('operator_name', array(
            'header' => Mage::helper('ghstatements')->__('Operator'),
            'sortable' => true,
            'width' => '60',
            'index' => 'operator_name',
        ));

        $this->addColumn("value", array(
            "index" => "value",
            "header" => Mage::helper("ghstatements")->__("Amount"),
            'type' => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/refundGrid', array('_current'=>true));
    }

}
