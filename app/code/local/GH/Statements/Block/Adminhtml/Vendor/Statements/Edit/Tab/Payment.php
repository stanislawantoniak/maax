<?php

class GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tab_Payment
    extends GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tab_Statement
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('statement_payment');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('zolagopayment/vendor_payment')
            ->getCollection()
            ->addFieldToFilter('statement_id', $this->getStatement()->getId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $_hlp = Mage::helper('zolagopayment');

        $this->addColumn('vendor_payment_id', array(
            'header' => $_hlp->__('ID'),
            'sortable' => true,
            'index' => 'vendor_payment_id'
        ));
        $this->addColumn('comment', array(
            'header' => $_hlp->__('Comment'),
            'sortable' => true,
            'index' => 'comment'
        ));
        $this->addColumn('date', array(
            'header' => $_hlp->__('Date'),
            'sortable' => true,
            'index' => 'date',
            'type' => 'date',
        ));
        $this->addColumn("cost", array(
            "index" => "cost",
            "header" => $_hlp->__("Payment amount"),
            'type' => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/paymentGrid', array('_current'=>true));
    }

}
