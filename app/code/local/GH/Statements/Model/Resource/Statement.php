<?php

/**
 * Resource for statement
 */
class GH_Statements_Model_Resource_Statement extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('ghstatements/statement', 'id');
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return GH_Statements_Model_Statement
     */
    public function _afterSave(Mage_Core_Model_Abstract $object)
    {

        $this->_recalculateBalanceDue($object->getData("event_date"), $object->getData("vendor_id"));

        return parent::_afterSave($object);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return GH_Statements_Model_Statement
     */
    public function _afterDelete(Mage_Core_Model_Abstract $object)
    {
        $this->_recalculateBalanceDue($object->getData("event_date"), $object->getData("vendor_id"));

        return parent::_afterDelete($object);
    }

    protected function _recalculateBalanceDue($date, $vendorId)
    {
        $dateFormatted = date("Y-m", strtotime($date));
        $statements = Mage::getModel("ghstatements/statement")
            ->getCollection()
            ->addFieldToFilter("vendor_id", $vendorId);
        $statements->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns("DATE_FORMAT(event_date,'%Y-%m') statement_month, (last_statement_balance+to_pay-payment_value) AS current_balance_of_the_settlement")
            ->having("statement_month=?", $dateFormatted)
            ->order("event_date DESC");

        $currentBalanceOfTheSettlement = $statements
            ->getFirstItem()
            ->getData("current_balance_of_the_settlement");


        Mage::helper("ghstatements/vendor_balance")
            ->updateVendorBalanceData(
                $vendorId,
                "balance_due",
                (float)$currentBalanceOfTheSettlement,
                $date
            );
    }
}