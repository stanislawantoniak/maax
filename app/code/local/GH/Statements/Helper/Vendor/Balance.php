<?php

/**
 * Class GH_Statements_Helper_Vendor_Balance
 */
class GH_Statements_Helper_Vendor_Balance extends Mage_Core_Helper_Abstract
{

    /**
     * @param $vendorId
     * @param $fieldToUpdate
     * @param $value
     * @param $dateNew
     * @param $dateOld
     */
    public function updateVendorBalanceData($vendorId, $fieldToUpdate, $value, $dateNew, $dateOld)
    {
        //krumo($dateNew, $dateOld);
        $dateNewFormatted = date("Y-m", strtotime($dateNew));
        $dateOldFormatted = date("Y-m", strtotime($dateOld));
        //krumo($dateNewFormatted, $dateOldFormatted);
        if ($dateNewFormatted != $dateOldFormatted) {
            //TODO change value in the old month
        }
        $vendorPayments = Mage::getModel("zolagopayment/vendor_payment")
            ->getCollection()
            ->addFieldToFilter("vendor_id", $vendorId);

        //krumo($vendorPayments->getSelect()->__toString());

        $vendorPayments->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns("SUM(CAST(cost AS DECIMAL(10,4)))  as total, DATE_FORMAT(date,'%Y-%m') AS month")
            ->having("month=?", $dateNewFormatted)
            ->group("month");
        //krumo($vendorPayments->getSelect()->__toString());
        //krumo($vendorPayments->getData());
        $vendorPaymentSum = $vendorPayments->getFirstItem()->getTotal();

        $vendorBalance = Mage::getModel("ghstatements/vendor_balance");

        $vendorBalance->addData(
            array(
                "vendor_id" => $vendorId,
                "date" => $dateNewFormatted,
                $fieldToUpdate => $vendorPaymentSum
            )
        );
        try {
            $vendorBalance->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}