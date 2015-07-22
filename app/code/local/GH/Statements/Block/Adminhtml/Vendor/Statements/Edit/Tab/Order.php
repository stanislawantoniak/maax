<?php

class GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tab_Order
    extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('statement_order');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    protected function getStatementId()
    {
        return $this->getRequest()->getParam("id");
    }

    public function getStatement()
    {

        $statement = Mage::registry('ghstatements_current_statement');
        if (!$statement) {
            $statement = Mage::getModel('ghstatements/statement')
                ->load($this->getStatementId());
            Mage::register('ghstatements_current_statement', $statement);
        }
        return $statement;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ghstatements/order')
            ->getCollection()
            ->addFieldToFilter('statement_id', $this->getStatement()->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('ghstatements')->__('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'id'
        ));
        $this->addColumn('po_increment_id', array(
            'header'    => Mage::helper('ghstatements')->__('Order number'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'po_increment_id'
        ));
        $this->addColumn('sku', array(
            'header'    => Mage::helper('ghstatements')->__('SKU'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'sku'
        ));
        $this->addColumn('shipped_date', array(
            'header'    => Mage::helper('ghstatements')->__('Shipping Date'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'shipped_date'
        ));
    }
}
