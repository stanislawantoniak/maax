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
        Mage::log($value, null, "importDataFromTransaction.log");
        Mage::log($dateNew, null, "importDataFromTransaction.log");
        $dateNewFormatted = date("Y-m", strtotime($dateNew));
        $dateOldFormatted = date("Y-m", strtotime($dateOld));
        Mage::log($dateNewFormatted, null, "importDataFromTransaction.log");
        if ($dateNewFormatted != $dateOldFormatted) {
            //TODO change value in the old month
        }

        $valueToUpdate = 0;
        switch ($fieldToUpdate) {
            case "vendor_payment_cost":
                $valueToUpdate = $this->getTotalVendorPaymentPerMonth($vendorId, $dateNewFormatted);
                break;
            case "vendor_invoice_cost":
                $valueToUpdate = $this->getTotalVendorInvoicePerMonth($vendorId, $dateNewFormatted);
                break;
            case "payment_from_client":
                Mage::log($value, null, "importDataFromTransaction.log");
                $valueToUpdate = $value;
                break;
        }

        if ($valueToUpdate == 0)
            return;


        $vendorBalance = Mage::getModel("ghstatements/vendor_balance");
        $vendorBalanceCollection = $vendorBalance->getCollection()
            ->addFieldToFilter("vendor_id", $vendorId)
            ->addFieldToFilter("date", $dateNewFormatted);

        $vendorBalanceItem = $vendorBalanceCollection->getFirstItem();

        //UPDATE (if a row with vendor and date already exist in the table)
        if ($vendorBalanceItem->getData("id")) {
            $id = $vendorBalanceItem->getData("id");
            try {
                $vendorBalanceLine = $vendorBalance->load($id);
                if($fieldToUpdate == "payment_from_client"){
                    $paymentFromClient = $vendorBalanceLine->getData("payment_from_client");
                    Mage::log("Total for line: ", null, "importDataFromTransaction.log");
                    Mage::log($paymentFromClient + $valueToUpdate, null, "importDataFromTransaction.log");
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
                        "date" => $dateNewFormatted,
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