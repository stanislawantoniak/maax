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
        $collection = Mage::getModel('ghstatements/refund')
            ->getCollection();
        $select = $collection->getSelect();
        $select->joinLeft(
            array('vendor' => Mage::getSingleton('core/resource')->getTableName('udropship/vendor')),
            'main_table.vendor_id = vendor.vendor_id',
            array(
                "refund_initiator" => new Zend_Db_Expr('IF(`operator_name` IS NOT NULL, `operator_name` , `vendor_name`)')
            )
        );
        $collection->addFieldToFilter('statement_id', $this->getStatement()->getId());

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
        $this->addColumn('refund_initiator', array(
            'header' => Mage::helper('ghstatements')->__("Refund Initiator"),
            'sortable' => true,
            'width' => '60',
            'index' => 'refund_initiator',
            'filter_condition_callback' => array($this, '_refundInitiatorFilter')
        ));

        $this->addColumn("registered_value", array(
            "index" => "registered_value",
            "header" => Mage::helper("ghstatements")->__("Sale value"),
            'type' => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));

        $this->addColumn("value", array(
            "index" => "value",
            "header" => Mage::helper("ghstatements")->__("To pay"),
            'type' => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
    }

    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    protected function _refundInitiatorFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $this->getCollection()->getSelect()->where(
            "operator_name LIKE ?
            OR vendor_name LIKE ?"
            , "%$value%");

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/refundGrid', array('_current'=>true));
    }

}
