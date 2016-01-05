<?php

/**
 * Class GH_Statements_Helper_Vendor_Balance
 */
class GH_Statements_Helper_Vendor_Balance extends Mage_Core_Helper_Abstract
{

    public function calculateVendorBalance()
    {
        // I. Collect values
        // 1. Customer payments (Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT)
        $customerPayments = $this->getCustomerPayments();
        //Mage::log($customerPayments, null, "TEST.log");
        // 2. Customer refunds (Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_REFUND)
        // 3. Payouts to vendor
        // 4. Invoices and credit notes

        // II. Calculate balances
        // 5. Balance
        // 5.1. Monthly balance
        // 5.2. Cumulative balance
        // 5.3. Due balance


        // III. Insert (update) balance table
    }
    /**
     * @param $vendorId
     * @param $fieldToUpdate
     * @param $value
     * @param $dateNew
     * @param bool|FALSE $dateOld
     */
    public function updateVendorBalanceData($vendorId, $fieldToUpdate, $value, $dateNew, $dateOld = FALSE)
    {
        $dateNewFormatted = date("Y-m", strtotime($dateNew));
        if ($dateOld) {
            $dateOldFormatted = date("Y-m", strtotime($dateOld));

            if ($dateNewFormatted != $dateOldFormatted) {
                //change value in the old month
                $this->calculateMonthLine($vendorId, $fieldToUpdate, $value, $dateOld);
            }
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

        $valueToUpdate = $value;
        switch ($fieldToUpdate) {
            case "vendor_payment_cost":
                $valueToUpdate = $this->getTotalVendorPaymentPerMonth($vendorId, $dateFormatted);
                break;
            case "vendor_invoice_cost":
                $valueToUpdate = $this->getTotalVendorInvoicePerMonth($vendorId, $dateFormatted);
                break;
        }

        $vendorBalance = Mage::getModel("ghstatements/vendor_balance");
        $vendorBalanceCollection = $vendorBalance->getCollection()
            ->addFieldToFilter("vendor_id", $vendorId)
            ->addFieldToFilter("date", $dateFormatted)
            //->addFieldToFilter("status", GH_Statements_Model_Vendor_Balance::GH_VENDOR_BALANCE_STATUS_OPENED)
        ;
        $vendorBalanceItem = $vendorBalanceCollection->getFirstItem();

        //UPDATE (if a row with vendor and date already exist in the table)
        $id = $vendorBalanceItem->getId();

        if ($id) {
            try {
                $vendorBalanceLine = $vendorBalance->load($id);

                switch ($fieldToUpdate) {
                    case "payment_from_client":
                        $paymentFromClient = $vendorBalanceLine->getData("payment_from_client");
                        $valueToUpdate = $valueToUpdate + $paymentFromClient;
                        break;
                    case "payment_return_to_client":
                        $paymentReturnToClient = $vendorBalanceLine->getData("payment_return_to_client");
                        $valueToUpdate = $valueToUpdate + $paymentReturnToClient;
                        break;
                }

                $vendorBalanceLine
                    ->setData($fieldToUpdate, (float)$valueToUpdate)
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
                        $fieldToUpdate => (float)$valueToUpdate
                    )
                );
                $vendorBalance->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    public function getCustomerPayments()
    {
        $customerPayments = array();

        $customerPaymentsCollection = Mage::getModel("zolagopayment/allocation")->getCollection();
        $customerPaymentsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns("vendor_id, SUM(CAST(allocation_amount AS DECIMAL(12,4)))  as amount, DATE_FORMAT(created_at,'%Y-%m') AS balance_month")
            ->where("allocation_type=?", Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT)
            ->group("vendor_id")->group("balance_month");
        //Mage::log($customerPaymentsCollection->getSelect()->__toString(), null, "TEST_1.log");
        //Reformat by vendor -> month
        foreach ($customerPaymentsCollection as $customerPaymentsItem) {
            $customerPayments[$customerPaymentsItem->getVendorId()][$customerPaymentsItem->getBalanceMonth()] = $customerPaymentsItem->getAmount();
        }
        return $customerPayments;
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
            ->columns("SUM(CAST(cost AS DECIMAL(12,4)))  as total, DATE_FORMAT(date,'%Y-%m') AS month")
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
        $vendorPaymentSum = 0;

        $dateFormatted = date("Y-m", strtotime($date));
        $vendorInvoices = Mage::getModel("zolagopayment/vendor_invoice")
            ->getCollection()
            ->addFieldToFilter("vendor_id", $vendorId);

        // faktury wg daty sprzedaży (a nie daty wystawienia)
        $vendorInvoices->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns("SUM(
                CAST(commission_brutto AS DECIMAL (12, 4))
                + CAST(transport_brutto AS DECIMAL (12, 4))
                + CAST(marketing_brutto AS DECIMAL (12, 4))
                + CAST(other_brutto AS DECIMAL (12, 4))
            )  as total, DATE_FORMAT(sale_date,'%Y-%m') AS month")
            ->where("is_invoice_correction=?", Zolago_Payment_Model_Vendor_Invoice::INVOICE_TYPE_ORIGINAL)
            ->having("month=?", $dateFormatted)
            ->group("month");

        $vendorInvoiceTotal = $vendorInvoices
            ->getFirstItem()
            ->getTotal();

        $vendorPaymentSum += $vendorInvoiceTotal;

        //korekty wg daty wystawienia

        $vendorInvoices2 = Mage::getModel("zolagopayment/vendor_invoice")
            ->getCollection()
            ->addFieldToFilter("vendor_id", $vendorId);

        $vendorInvoices2->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns("SUM(
                CAST(commission_brutto AS DECIMAL (12, 4))
                + CAST(transport_brutto AS DECIMAL (12, 4))
                + CAST(marketing_brutto AS DECIMAL (12, 4))
                + CAST(other_brutto AS DECIMAL (12, 4))
            )  as total, DATE_FORMAT(date,'%Y-%m') AS month")
            ->where("is_invoice_correction=?", Zolago_Payment_Model_Vendor_Invoice::INVOICE_TYPE_CORRECTION)
            ->having("month=?", $dateFormatted)
            ->group("month");

        $vendorInvoiceCorrectionTotal = $vendorInvoices2
            ->getFirstItem()
            ->getTotal();

        $vendorPaymentSum += $vendorInvoiceCorrectionTotal;

        return $vendorPaymentSum;
    }
}