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
        $dateNewFormatted = date("Y-m", strtotime($dateNew));
        $dateOldFormatted = date("Y-m", strtotime($dateOld));

        if ($dateNewFormatted != $dateOldFormatted) {
            //change value in the old month
            $this->calculateMonthLine($vendorId, $fieldToUpdate, $value, $dateOld);
        }

        $this->calculateMonthLine($vendorId, $fieldToUpdate, $value, $dateNew);

    }

    /**
     * @param $vendorId
     * @param $fieldToUpdate
     * @param $value
     * @param $date
     */
    public function calculateMonthLine($vendorId, $fieldToUpdate, $value, $date)
    {
        $dateFormatted = date("Y-m", strtotime($date));

        $valueToUpdate = 0;
        switch ($fieldToUpdate) {
            case "vendor_payment_cost":
                $valueToUpdate = $this->getTotalVendorPaymentPerMonth($vendorId, $dateFormatted);
                break;
            case "vendor_invoice_cost":
                $valueToUpdate = $this->getTotalVendorInvoicePerMonth($vendorId, $dateFormatted);
                break;
            case "payment_from_client":
                $valueToUpdate = $value;
                break;
        }

        //Nothing to update
        if ($valueToUpdate == 0)
            return;


        $vendorBalance = Mage::getModel("ghstatements/vendor_balance");
        $vendorBalanceCollection = $vendorBalance->getCollection()
            ->addFieldToFilter("vendor_id", $vendorId)
            ->addFieldToFilter("date", $dateFormatted);
        $vendorBalanceItem = $vendorBalanceCollection->getFirstItem();

        //UPDATE (if a row with vendor and date already exist in the table)
        $id = $vendorBalanceItem->getId();
        if ($id && $vendorBalanceItem->getStatus() == GH_Statements_Model_Vendor_Balance::GH_VENDOR_BALANCE_STATUS_OPENED) {
            try {
                $vendorBalanceLine = $vendorBalance->load($id);
                if ($fieldToUpdate == "payment_from_client") {
                    $paymentFromClient = $vendorBalanceLine->getData("payment_from_client");
                    $valueToUpdate = $valueToUpdate + $paymentFromClient;
                }

                $vendorBalanceLine
                    ->setData($fieldToUpdate, $valueToUpdate)
                    ->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        } else {
            //OR
            //INSERT
            try {
                $vendorBalance->addData(
                    array(
                        "vendor_id" => $vendorId,
                        "date" => $dateFormatted,
                        $fieldToUpdate => $valueToUpdate
                    )
                );
                $vendorBalance->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }


    /**
     * @param $vendorId
     * @param $date
     * @return mixed
     */
    public function getTotalVendorPaymentPerMonth($vendorId, $date)
    {
        $dateFormatted = date("Y-m", strtotime($date));
        $vendorPayments = Mage::getModel("zolagopayment/vendor_payment")
            ->getCollection()
            ->addFieldToFilter("vendor_id", $vendorId);


        $vendorPayments->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns("SUM(CAST(cost AS DECIMAL(10,4)))  as total, DATE_FORMAT(date,'%Y-%m') AS month")
            ->having("month=?", $dateFormatted)
            ->group("month");

        $vendorPaymentSum = $vendorPayments->getFirstItem()->getTotal();
        return $vendorPaymentSum;
    }

    /**
     * @param $vendorId
     * @param $date
     * @return mixed
     */
    public function getTotalVendorInvoicePerMonth($vendorId, $date)
    {
        $dateFormatted = date("Y-m", strtotime($date));
        $vendorInvoices = Mage::getModel("zolagopayment/vendor_invoice")
            ->getCollection()
            ->addFieldToFilter("vendor_id", $vendorId);

        $vendorInvoices->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns("SUM(
                CAST(commission_brutto AS DECIMAL (10, 4))
                + CAST(transport_brutto AS DECIMAL (10, 4))
                + CAST(marketing_brutto AS DECIMAL (10, 4))
                + CAST(other_brutto AS DECIMAL (10, 4))
            )  as total, DATE_FORMAT(date,'%Y-%m') AS month")
            ->having("month=?", $dateFormatted)
            ->group("month");

        $vendorPaymentSum = $vendorInvoices->getFirstItem()->getTotal();
        return $vendorPaymentSum;
    }
}