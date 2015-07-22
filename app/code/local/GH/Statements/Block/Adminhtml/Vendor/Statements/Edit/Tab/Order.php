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

    public function getStatement()
    {
        $statement = Mage::registry('ghstatements_current_statement');
        if (!$statement) {
            $statement = Mage::getModel('ghstatement/statement')->load($this->getId());
            Mage::register('ghstatements_current_statement', $statement);
        }
        return $statement;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ghstatement/statement_orders')->getCollection()
            ->addFieldToFilter('id', $this->getStatement()->gettId());;

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
}
