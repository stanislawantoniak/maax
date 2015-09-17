<?php

class GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tab_Statement
    extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('statement_refund');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
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
}
