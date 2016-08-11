<?php

class GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tab_Marketing
    extends GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tab_Statement
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('statement_marketing');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {

        /**
         *
         * SELECT
        `main_table`.* ,
        IF(`operator_name` IS NOT NULL, `operator_name`,vendor_name ) AS refund_initiator
        FROM
        `gh_statements_refunds` AS `main_table`
        LEFT JOIN `udropship_vendor` AS vendor ON vendor.vendor_id=main_table.vendor_id
        WHERE (statement_id = %id%)
         */
        $collection = Mage::getModel('ghstatements/marketing')
            ->getCollection()
            ->addFieldToFilter('statement_id', $this->getStatement()->getId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $_hlp = Mage::helper('ghstatements');

        $this->addColumn('id', array(
            'header' => $_hlp->__('ID'),
            'sortable' => true,
            'index' => 'id'
        ));
        $this->addColumn('product_sku', array(
            'header' => $_hlp->__('SKU'),
            'sortable' => true,
            'index' => 'product_sku'
        ));
        $this->addColumn('product_vendor_sku', array(
            'header' => $_hlp->__('Vendor SKU'),
            'sortable' => true,
            'index' => 'product_vendor_sku'
        ));
        $this->addColumn('product_name', array(
            'header' => $_hlp->__('Product name'),
            'sortable' => true,
            'index' => 'product_name'
        ));
        $this->addColumn('marketing_cost_type_name', array(
            'header' => $_hlp->__('Marketing cost type'),
            'sortable' => true,
            'index' => 'marketing_cost_type_name'
        ));
        $this->addColumn('date', array(
            'header' => $_hlp->__('Date'),
            'sortable' => true,
            'index' => 'date',
            'type' => 'date',
        ));
        $this->addColumn("value", array(
            "index" => "value",
            "header" => $_hlp->__("Marketing amount"),
            'type' => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/marketingGrid', array('_current'=>true));
    }

}
