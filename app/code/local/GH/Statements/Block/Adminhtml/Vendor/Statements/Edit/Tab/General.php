<?php

class GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Container
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function canShowTab()
    {
        return true;
    }

    public function getTabLabel()
    {
        return Mage::helper('ghstatements')->__("Statement Info");
    }

    public function getTabTitle()
    {
        return Mage::helper('ghstatements')->__("General Information");
    }

    public function isHidden()
    {
        return false;
    }

    public function _toHtml()
    {
        $this->setTemplate('ghstatements/vendor/statements/tab/general.phtml');

        return parent::_toHtml();
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
