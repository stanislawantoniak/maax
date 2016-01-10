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
    public function _afterDelete(Mage_Core_Model_Abstract $object)
    {
//        $this->_recalculateBalanceDue($object->getData("vendor_id"), $object->getData("event_date"));
        Mage::helper("ghstatements/vendor_balance")
            ->calculateVendorBalance(
                date("Y-m", strtotime($object->getData("event_date"))),
                $object->getData("vendor_id")
            );
        return parent::_afterDelete($object);
    }

    /**
     * @param $vendorId
     * @param $date
     */
    protected function _recalculateBalanceDue($vendorId, $date)
    {
        $dateFormatted = date("Y-m", strtotime($date));
        $statements = Mage::getModel("ghstatements/statement")
            ->getCollection()
            ->addFieldToFilter("vendor_id", $vendorId);
        $statements->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns("DATE_FORMAT(event_date,'%Y-%m') statement_month, actual_balance")
            ->having("statement_month=?", $dateFormatted)
            ->order("event_date DESC");

        $actualBalance = $statements
            ->getFirstItem()
            ->getData("actual_balance");


        Mage::helper("ghstatements/vendor_balance")
            ->updateVendorBalanceData(
                $vendorId,
                "balance_due",
                (float)$actualBalance,
                $date
            );
    }
}