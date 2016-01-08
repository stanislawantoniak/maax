<?php

/**
 * Class GH_Statements_Helper_Vendor_Balance
 */
class GH_Statements_Helper_Vendor_Balance extends Mage_Core_Helper_Abstract
{

    /**
     * Calculate vendor balance for opened months
     */
    public function calculateVendorBalance()
    {
        $data = array();

        //I. Fetch closed month
        //Don't touch closed months!!!
        $closedBalanceMonths = $this->getClosedVendorBalanceMonths();
        //Mage::log($closedBalanceMonths, null, "TEST_SALDO_CLOSED.log");


        // II. Collect values
        // 1. Customer payments (Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT)
        $customerPayments = $this->getCustomerPayments();
        //Mage::log($customerPayments, null, "TEST_SALDO_PAYMENTS.log");
        $data = $this->collectDataBeforeBalanceUpdate($customerPayments, "payment_from_client", $data, $closedBalanceMonths);

        // 2. Customer refunds (Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_REFUND)
        $customerRefunds = $this->getCustomerRefunds();
        //Mage::log($customerRefunds, null, "TEST_SALDO_REFUNDS.log");
        $data = $this->collectDataBeforeBalanceUpdate($customerRefunds, "payment_return_to_client", $data, $closedBalanceMonths);

        // 3. Payouts to vendor
        $vendorPayouts = $this->getVendorPayouts();
        //Mage::log($vendorPayouts, null, "TEST_SALDO_PAYOUTS.log");
        $data = $this->collectDataBeforeBalanceUpdate($vendorPayouts, "vendor_payment_cost", $data, $closedBalanceMonths);

        // 4. Invoices and credit notes
        $vendorInvoices = $this->getVendorInvoices();
        //Mage::log($vendorInvoices, null, "TEST_SALDO_INVOICES_RESULT.log");
        $data = $this->collectDataBeforeBalanceUpdate($vendorInvoices, "vendor_invoice_cost", $data, $closedBalanceMonths);


        // III. Calculate balances
        // 5. Balance
        // 5.1. Monthly balance (updates in GH_Statements_Model_Vendor_Balance::_beforeSave)
        // 5.2. Cumulative balance (updates in GH_Statements_Model_Vendor_Balance::_afterSave)

        // 5.3. Due balance
        $balanceDue = $this->getBalanceDue();
        //Mage::log($balanceDue, null, "TEST_SALDO_BALANCEDUE.log");
        $data = $this->collectDataBeforeBalanceUpdate($balanceDue, "balance_due", $data, $closedBalanceMonths);

        // IV. Insert (update) balance table
        //Mage::log($data, null, "TEST_SALDO_BALANCE.log");
        if (empty($data)) {
            //Nothing to update
            return;
        }

        $toUpdate = array();
        foreach ($data as $vendorId => $dataItem) {
            foreach ($dataItem as $month => $dataMonthItem) {
                foreach ($dataMonthItem as $field => $amount) {

                    $dataToUpdate = array(
                        "vendor_id" => $vendorId,
                        "date" => $month,
                        $field => $amount
                    );
                    if (isset($toUpdate[$vendorId][$month])) {
                        $toUpdate[$vendorId][$month] = array_merge($toUpdate[$vendorId][$month], $dataToUpdate);
                    } else {
                        $toUpdate[$vendorId][$month] = $dataToUpdate;
                    }

                }
            }
        }
        if (!empty($toUpdate)){
            foreach ($toUpdate as $vendorId => $toUpdateItem) {
                foreach ($toUpdateItem as $date => $dataLine) {
                    $this->updateBalanceMonthLine($dataLine);
                }
            }
            //Mage::log($toUpdate, null, "TEST_SALDO_RESULT.log");
        }

    }

    /**
     * @param $input
     * @param $field
     * @param $collector
     * @param array $closedBalanceMonths
     * @return mixed
     */
    public function collectDataBeforeBalanceUpdate($input, $field, $collector, $closedBalanceMonths = array())
    {
        if (empty($input))
            return $collector;

        foreach ($input as $vendorId => $customerPayment) {
            foreach ($customerPayment as $month => $amount) {
                if (!isset($closedBalanceMonths[$vendorId][$month]))
                    $collector[$vendorId][$month][$field] = $amount;
            }
        }
        return $collector;
    }


    /**
     * Get last statement of the month
     * @return array
     */
    public function getBalanceDue()
    {
        $balanceDue = array();
        $statements = Mage::getModel("ghstatements/statement")
            ->getCollection();
        $statements->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns("vendor_id, last_statement_balance,to_pay,payment_value, DATE_FORMAT(event_date,'%Y-%m') AS balance_month")
            ->order("event_date DESC");
        Mage::log($statements->getSelect()->__toString(), null, "TEST_SALDO_DUE.log");
        //Reformat by vendor
        foreach ($statements as $statement) {
            $B = $statement->getLastStatementBalance();
            $A = $statement->getToPay();
            $C = $statement->getPaymentValue();
            if(isset($balanceDue[$statement->getVendorId()][$statement->getBalanceMonth()]))
                continue;

            $balanceDue[$statement->getVendorId()][$statement->getBalanceMonth()] = sprintf("%.4f", round($B + $A - $C, 2));



        }

        return $balanceDue;
    }

    /**
     * @return array
     */
    public function getClosedVendorBalanceMonths()
    {
        $closedMonths = array();
        $vendorBalance = Mage::getModel("ghstatements/vendor_balance");
        $closedVendorBalancesCollection = $vendorBalance->getCollection()
            ->addFieldToFilter("status", GH_Statements_Model_Vendor_Balance::GH_VENDOR_BALANCE_STATUS_CLOSED);

        //reformat by vendor => month
        foreach ($closedVendorBalancesCollection as $closedVendorBalancesItem) {
            $closedMonths[$closedVendorBalancesItem->getVendorId()][$closedVendorBalancesItem->getDate()] = $closedVendorBalancesItem->getDate();
        }
        return $closedMonths;
    }

    /**
     * @param $data
     */
    public function updateBalanceMonthLine($data)
    {
        $vendorId = $data["vendor_id"];
        $dateFormatted = $data["date"];

        $vendorBalance = Mage::getModel("ghstatements/vendor_balance");
        $vendorBalanceCollection = $vendorBalance->getCollection()
            ->addFieldToFilter("vendor_id", $vendorId)
            ->addFieldToFilter("date", $dateFormatted);
        $vendorBalanceItem = $vendorBalanceCollection->getFirstItem();

        //UPDATE (if a row with vendor and date already exist in the table)
        $id = $vendorBalanceItem->getId();
        $vendorBalanceModel = $vendorBalance->load($id);
        if ($vendorBalanceModel && $vendorBalanceModel->getStatus() == GH_Statements_Model_Vendor_Balance::GH_VENDOR_BALANCE_STATUS_CLOSED)
            return;

        try {
            $vendorBalanceLine = $vendorBalanceModel->addData($data);
            $vendorBalanceLine->setId($id)->save();

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Customers payments
     * @return array
     */
    public function getCustomerPayments()
    {
        $customerPayments = array();

        $customerPaymentsCollection = Mage::getModel("zolagopayment/allocation")->getCollection();
        $customerPaymentsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns("vendor_id, SUM(CAST(allocation_amount AS DECIMAL(12,4)))  as amount, DATE_FORMAT(created_at,'%Y-%m') AS balance_month")
            ->where("allocation_type=?", Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT)
            ->where("`primary`=?",1)
            ->group("vendor_id")
            ->group("balance_month");
        //Mage::log($customerPaymentsCollection->getSelect()->__toString(), null, "TEST_SALDO_PAYMENTS.log");

      //  Mage::log($results, null, "TEST_SALDO_PAYMENTS.log");
        //Reformat by vendor -> month
        foreach ($customerPaymentsCollection as $customerPaymentsItem) {
            $customerPayments[$customerPaymentsItem->getVendorId()][$customerPaymentsItem->getBalanceMonth()] = $customerPaymentsItem->getAmount();
        }
        return $customerPayments;
    }

    /**
     * Customers refunds
     * @return array
     */
    public function getCustomerRefunds(){
        $customerRefunds = array();

        $customerRefundsCollection = Mage::getModel("zolagopayment/allocation")->getCollection();
        $customerRefundsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns("vendor_id, SUM(CAST(allocation_amount AS DECIMAL(12,4)))  as amount, DATE_FORMAT(created_at,'%Y-%m') AS balance_month")
            ->where("allocation_type=?", Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_REFUND)
            ->group("vendor_id")
            ->group("balance_month");
        //Mage::log($customerRefundsCollection->getSelect()->__toString(), null, "TEST_SALDO_REFUNDS.log");
        //Reformat by vendor -> month
        foreach ($customerRefundsCollection as $customerRefundsItem) {
            $customerRefunds[$customerRefundsItem->getVendorId()][$customerRefundsItem->getBalanceMonth()] = $customerRefundsItem->getAmount();
        }
        return $customerRefunds;
    }

    /**
     * Vendor payouts
     * @return array
     */
    public function getVendorPayouts(){
        $vendorPayouts = array();

        $vendorPayoutsCollection = Mage::getModel("zolagopayment/vendor_payment")->getCollection();
        $vendorPayoutsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns("vendor_id, SUM(CAST(cost AS DECIMAL(12,4)))  as amount, DATE_FORMAT(date,'%Y-%m') AS balance_month")
            ->group("vendor_id")
            ->group("balance_month");
        //Mage::log($vendorPayoutsCollection->getSelect()->__toString(), null, "TEST_SALDO_PAYOUTS.log");
        //Reformat by vendor -> month
        foreach ($vendorPayoutsCollection as $vendorPayoutsItem) {
            $vendorPayouts[$vendorPayoutsItem->getVendorId()][$vendorPayoutsItem->getBalanceMonth()] = $vendorPayoutsItem->getAmount();
        }
        return $vendorPayouts;
    }

    /**
     * Vendor invoices
     * Two parts:
     * 1. faktury wg daty sprzedaży (a nie daty wystawienia)
     * 2. korekty wg daty wystawienia
     *
     * @return array
     */
    public function getVendorInvoices()
    {
        $result = array();

        $vendorDateHelper = array();

        // 1. faktury wg daty sprzedaży (a nie daty wystawienia)
        $vendorInvoices = array();
        $vendorInvoicesCollection = Mage::getModel("zolagopayment/vendor_invoice")->getCollection();
        $vendorInvoicesCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns("vendor_id,
             SUM(
                CAST(commission_brutto AS DECIMAL (12, 4))
                + CAST(transport_brutto AS DECIMAL (12, 4))
                + CAST(marketing_brutto AS DECIMAL (12, 4))
                + CAST(other_brutto AS DECIMAL (12, 4))
            )  as amount, DATE_FORMAT(sale_date,'%Y-%m') AS balance_month")
            ->where("is_invoice_correction=?", Zolago_Payment_Model_Vendor_Invoice::INVOICE_TYPE_ORIGINAL)
            ->where("wfirma_invoice_number != '' ")
            ->group("vendor_id")
            ->group("balance_month");
        //Mage::log($vendorInvoicesCollection->getSelect()->__toString(), null, "TEST_SALDO_INVOICES_ORIGINAL.log");
        //Reformat by vendor -> month
        foreach ($vendorInvoicesCollection as $vendorInvoicesItem) {
            $vendorInvoices[$vendorInvoicesItem->getVendorId()][$vendorInvoicesItem->getBalanceMonth()] = $vendorInvoicesItem->getAmount();
            $vendorDateHelper[$vendorInvoicesItem->getVendorId()][$vendorInvoicesItem->getBalanceMonth()] = $vendorInvoicesItem->getBalanceMonth();
        }
       //Mage::log($vendorInvoices, null, "TEST_SALDO_INVOICES_ORIGINAL.log");

        //2. korekty wg daty wystawienia
        $vendorCorrections = array();
        $vendorInvoiceCorrectionsCollection = Mage::getModel("zolagopayment/vendor_invoice")->getCollection();
        $vendorInvoiceCorrectionsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns("vendor_id,
             SUM(
                CAST(commission_brutto AS DECIMAL (12, 4))
                + CAST(transport_brutto AS DECIMAL (12, 4))
                + CAST(marketing_brutto AS DECIMAL (12, 4))
                + CAST(other_brutto AS DECIMAL (12, 4))
            )  as amount, DATE_FORMAT(date,'%Y-%m') AS balance_month")
            ->where("is_invoice_correction=?", Zolago_Payment_Model_Vendor_Invoice::INVOICE_TYPE_CORRECTION)
            ->where("wfirma_invoice_number != '' ")
            ->group("vendor_id")
            ->group("balance_month");
        //Mage::log($vendorInvoiceCorrectionsCollection->getSelect()->__toString(), null, "TEST_SALDO_INVOICES_CORRECTION.log");
        //Reformat by vendor -> month
        foreach ($vendorInvoiceCorrectionsCollection as $vendorInvoiceCorrectionsItem) {
            $vendorCorrections[$vendorInvoiceCorrectionsItem->getVendorId()][$vendorInvoiceCorrectionsItem->getBalanceMonth()] = $vendorInvoiceCorrectionsItem->getAmount();
            $vendorDateHelper[$vendorInvoiceCorrectionsItem->getVendorId()][$vendorInvoiceCorrectionsItem->getBalanceMonth()] = $vendorInvoiceCorrectionsItem->getBalanceMonth();
        }
        //Mage::log($vendorCorrections, null, "TEST_SALDO_INVOICES_CORRECTION.log");

        //Mage::log($vendorCorrections, null, "TEST_SALDO_INVOICES_CORRECTION.log");
        //3. Calculate sum
        if (empty($vendorDateHelper))
            return $result;

        foreach ($vendorDateHelper as $vendorId => $months) {
            foreach($months as $month){
                $invoiceAmount = isset($vendorInvoices[$vendorId][$month]) ? $vendorInvoices[$vendorId][$month] : 0;
                $correctionAmount = isset($vendorCorrections[$vendorId][$month]) ? $vendorCorrections[$vendorId][$month] : 0;
                //if (($invoiceAmount + $correctionAmount) > 0)
                    $result[$vendorId][$month] = $invoiceAmount + $correctionAmount;
            }
        }
        //Mage::log($result, null, "TEST_SALDO_INVOICES_RESULT.log");
        return $result;
    }






    /*On delete handlers*/
    /**
     * @param $vendorId
     * @param $fieldToUpdate
     * @param $value
     * @param $date
     */
    public function updateVendorBalanceData($vendorId, $fieldToUpdate, $value, $date)
    {
        $this->calculateMonthLine($vendorId, $fieldToUpdate, $value, $date);
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
            ->addFieldToFilter("date", $dateFormatted)//->addFieldToFilter("status", GH_Statements_Model_Vendor_Balance::GH_VENDOR_BALANCE_STATUS_OPENED)
        ;
        $vendorBalanceItem = $vendorBalanceCollection->getFirstItem();

        //UPDATE (if a row with vendor and date already exist in the table)
        $id = $vendorBalanceItem->getId();

        $data = array(
            "vendor_id" => $vendorId,
            "date" => $dateFormatted,
            $fieldToUpdate => (float)$valueToUpdate
        );
        $vendorBalanceModel = $vendorBalance->load($id);
        try {
            $vendorBalanceLine = $vendorBalanceModel->addData($data);
            $vendorBalanceLine->setId($id)->save();

        } catch (Exception $e) {
            Mage::logException($e);
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
            ->where("wfirma_invoice_number != '' ")
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
            ->where("wfirma_invoice_number != '' ")
            ->having("month=?", $dateFormatted)
            ->group("month");

        $vendorInvoiceCorrectionTotal = $vendorInvoices2
            ->getFirstItem()
            ->getTotal();

        $vendorPaymentSum += $vendorInvoiceCorrectionTotal;

        return $vendorPaymentSum;
    }
}