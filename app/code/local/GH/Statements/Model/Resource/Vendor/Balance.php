<?php

/**
 * Class GH_Statements_Model_Resource_Vendor_Balance
 */
class GH_Statements_Model_Resource_Vendor_Balance extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('ghstatements/vendor_balance', 'id');
    }

    /**
     * Perform actions after object save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $paymentFromClient = $object->getData("payment_from_client");

        $paymentReturnToClient = $object->getPaymentReturnToClient();
        $vendorPaymentCost = $object->getVendorPaymentCost();
        $vendorInvoiceCost = $object->getVendorInvoiceCost();

        $balancePerMonth = $paymentFromClient - $paymentReturnToClient - $vendorPaymentCost - $vendorInvoiceCost;
        $object->setData("balance_per_month", $balancePerMonth);

        return parent::_beforeSave($object);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    public function _afterSave(Mage_Core_Model_Abstract $object)
    {
        return parent::_afterSave($object);
    }

    /**
     * @throws Exception
     */
    public function calculateVendorBalanceData()
    {
        $this->_getWriteAdapter()->beginTransaction();
        try {
            $this->calculateVendorBalance();
            $this->_getWriteAdapter()->commit();
        } catch (Exception $e) {
            $this->_getWriteAdapter()->rollBack();
            throw $e;
        }
    }

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

        //II. Clear old values
        $this->clearVendorBalanceActiveMonthsBalances();

        //die("test");


        // III. Collect values
        // 1. Customer payments (Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT)
        $customerPayments = $this->getCustomerPayments();
        Mage::log($customerPayments, null, "TEST_SALDO_PAYMENTS.log");
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


        // 5.3. Due balance
        $balanceDue = $this->getBalanceDue();
        //Mage::log($balanceDue, null, "TEST_SALDO_BALANCEDUE.log");
        $data = $this->collectDataBeforeBalanceUpdate($balanceDue, "balance_due", $data, $closedBalanceMonths);


        if (empty($data)) {
            //Nothing to update
            // 5.2. Cumulative balance
            $this->recalculateBalanceCumulative();
            //Delete empty lines for not active vendors
            $this->removeInactiveVendorEmptyLines();
            return;
        }

        // IV. Insert (update) balance table
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
        if (!empty($toUpdate)) {
            foreach ($toUpdate as $vendorId => $toUpdateItem) {
                foreach ($toUpdateItem as $date => $dataLine) {
                    $this->updateBalanceMonthLine($dataLine);
                }
            }
        }


        // V. Calculate balances
        // 5. Balance
        // 5.1. Monthly balance (updates in GH_Statements_Model_Vendor_Balance::_beforeSave)

        // 5.2. Cumulative balance
        $this->recalculateBalanceCumulative();
        //Delete empty lines for not active vendors
        $this->removeInactiveVendorEmptyLines();

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
        //Mage::log($statements->getSelect()->__toString(), null, "TEST_SALDO_DUE.log");
        //Reformat by vendor
        foreach ($statements as $statement) {
            $B = $statement->getLastStatementBalance();
            $A = $statement->getToPay();
            $C = $statement->getPaymentValue();
            if (isset($balanceDue[$statement->getVendorId()][$statement->getBalanceMonth()]))
                continue;

            $balanceDue[$statement->getVendorId()][$statement->getBalanceMonth()] = sprintf("%.4f", round($B + $A - $C, 2));
        }

        return $balanceDue;
    }

    /**
     * @return $this
     */
    public function clearVendorBalanceActiveMonthsBalances()
    {
        $vendorBalance = Mage::getModel("ghstatements/vendor_balance");
        $openVendorBalancesCollection = $vendorBalance->getCollection()
            ->addFieldToFilter("status", GH_Statements_Model_Vendor_Balance::GH_VENDOR_BALANCE_STATUS_OPENED);

        //reformat by vendor => month
        foreach ($openVendorBalancesCollection as $openVendorBalancesCollectionItem) {
            //krumo($openVendorBalancesCollectionItem->getData());
            $openVendorBalancesCollectionItem->addData(
                array(
                    "payment_from_client" => 0,
                    "payment_return_to_client" => 0,
                    "vendor_payment_cost" => 0,
                    "vendor_invoice_cost" => 0,
                    "balance_per_month" => 0,
                    "balance_cumulative" => 0,
                    "balance_due" => 0
                )
            );
            //krumo($openVendorBalancesCollectionItem->getData());
            $openVendorBalancesCollectionItem->save();
        }
        return $this;
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
		/** @var GH_Statements_Helper_Vendor_Balance $hlpBalance */
		$hlpBalance = Mage::helper("ghstatements/vendor_balance");
		$config = $hlpBalance->getDotpaysPaymentChannelOwnerForStores();
		$mallDotpayIds =
            isset($config[Zolago_Payment_Model_Source_Channel_Owner::OWNER_MALL]) ?
            $config[Zolago_Payment_Model_Source_Channel_Owner::OWNER_MALL] :
            array();
        $customerPayments = array();

		/** @var Zolago_Payment_Model_Resource_Allocation_Collection $customerPaymentsCollection */
        $customerPaymentsCollection = Mage::getModel("zolagopayment/allocation")->getCollection();
        $customerPaymentsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)

            //  DATE_FORMAT(CONVERT_TZ(main_table.created_at,'GMT', 'Europe/Warsaw'),'%Y-%m') balance_month
            ->columns("main_table.vendor_id, SUM(CAST(main_table.allocation_amount AS DECIMAL(12,4))) as amount, DATE_FORMAT(CONVERT_TZ(main_table.created_at,'GMT', 'Europe/Warsaw'),'%Y-%m') AS balance_month")
			// Only transactions from our dotpay
			->joinLeft(
				array('spt' => $this->getTable('sales/payment_transaction')),
				'main_table.transaction_id = spt.transaction_id',
				array())
			->where("spt.dotpay_id IN (?)", $mallDotpayIds)
            ->where("main_table.allocation_type = ?", Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT)
            ->where("main_table.primary = ?", 1)
            ->group("main_table.vendor_id")
            ->group("balance_month");
        Mage::log($customerPaymentsCollection->getSelect()->__toString(), null, "TEST_SALDO_PAYMENTS.log");

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
    public function getCustomerRefunds()
    {
		/** @var GH_Statements_Helper_Vendor_Balance $hlpBalance */
		$hlpBalance = Mage::helper("ghstatements/vendor_balance");
		$config = $hlpBalance->getDotpaysPaymentChannelOwnerForStores();
		$mallDotpayIds =
            isset($config[Zolago_Payment_Model_Source_Channel_Owner::OWNER_MALL]) ?
                $config[Zolago_Payment_Model_Source_Channel_Owner::OWNER_MALL] :
                array();
        $customerRefunds = array();

		/** @var Zolago_Payment_Model_Resource_Allocation_Collection $customerRefundsCollection */
        $customerRefundsCollection = Mage::getModel("zolagopayment/allocation")->getCollection();
        $customerRefundsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)

            //  DATE_FORMAT(CONVERT_TZ(main_table.created_at,'GMT', 'Europe/Warsaw'),'%Y-%m') balance_month
            ->columns("main_table.vendor_id, SUM(CAST(main_table.allocation_amount AS DECIMAL(12,4))) as amount, DATE_FORMAT(CONVERT_TZ(main_table.created_at,'GMT', 'Europe/Warsaw'),'%Y-%m') AS balance_month")
			// Only transactions from our dotpay
			->joinLeft(
				array('spt' => $this->getTable('sales/payment_transaction')),
				'main_table.transaction_id = spt.transaction_id',
				array())
			->where("spt.dotpay_id IN (?)", $mallDotpayIds)
            ->where("main_table.allocation_type=?", Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_REFUND)
            ->group("main_table.vendor_id")
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
    public function getVendorPayouts()
    {
        $vendorPayouts = array();

        $vendorPayoutsCollection = Mage::getModel("zolagopayment/vendor_payment")->getCollection();
        $vendorPayoutsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)

            //  DATE_FORMAT(CONVERT_TZ(date,'GMT', 'Europe/Warsaw'),'%Y-%m') balance_month
            ->columns("vendor_id, SUM(CAST(cost AS DECIMAL(12,4)))  as amount, DATE_FORMAT(CONVERT_TZ(date,'GMT', 'Europe/Warsaw'),'%Y-%m') AS balance_month")
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

            //  DATE_FORMAT(CONVERT_TZ(sale_date,'GMT', 'Europe/Warsaw'),'%Y-%m') balance_month
            ->columns("vendor_id,
             SUM(
                CAST(commission_brutto AS DECIMAL (12, 4))
                + CAST(transport_brutto AS DECIMAL (12, 4))
                + CAST(marketing_brutto AS DECIMAL (12, 4))
                + CAST(other_brutto AS DECIMAL (12, 4))
            )  as amount, DATE_FORMAT(CONVERT_TZ(sale_date,'GMT', 'Europe/Warsaw'),'%Y-%m') AS balance_month")
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

            //  DATE_FORMAT(CONVERT_TZ(date,'GMT', 'Europe/Warsaw'),'%Y-%m') balance_month
            ->columns("vendor_id,
             SUM(
                CAST(commission_brutto AS DECIMAL (12, 4))
                + CAST(transport_brutto AS DECIMAL (12, 4))
                + CAST(marketing_brutto AS DECIMAL (12, 4))
                + CAST(other_brutto AS DECIMAL (12, 4))
            )  as amount, DATE_FORMAT(CONVERT_TZ(date,'GMT', 'Europe/Warsaw'),'%Y-%m') AS balance_month")
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
            foreach ($months as $month) {
                $invoiceAmount = isset($vendorInvoices[$vendorId][$month]) ? $vendorInvoices[$vendorId][$month] : 0;
                $correctionAmount = isset($vendorCorrections[$vendorId][$month]) ? $vendorCorrections[$vendorId][$month] : 0;
                //if (($invoiceAmount + $correctionAmount) > 0)
                $result[$vendorId][$month] = $invoiceAmount + $correctionAmount;
            }
        }
        //Mage::log($result, null, "TEST_SALDO_INVOICES_RESULT.log");
        return $result;
    }


    /**
     * balance_cumulative
     */
    public function recalculateBalanceCumulative()
    {
        $balances = Mage::getModel("ghstatements/vendor_balance")
            ->getCollection()
            ->setOrder("date", Varien_Data_Collection_Db::SORT_ORDER_ASC);

        $vendorsToRecalculateBalance = array();

        $balancesByVendor = array();
        foreach ($balances as $balance) {
            $balancesByVendor[$balance->getVendorId()][$balance->getDate()] = $balance->getData();
            $vendorsToRecalculateBalance[$balance->getVendorId()] = $balance->getVendorId();
            //arsort($balancesByVendor[$balance->getVendorId()]);
        }

        //unset not active vendors
        $activeVendorsToRecalculateBalance = array();
        $vendorsCollection = Mage::getModel("udropship/vendor")->getCollection();
        $vendorsCollection->addFieldToFilter("vendor_id", array("in", $vendorsToRecalculateBalance));
        $vendorsCollection->addFieldToFilter("status", array("in" => array(ZolagoOs_OmniChannel_Model_Source::VENDOR_STATUS_ACTIVE, ZolagoOs_OmniChannel_Model_Source::VENDOR_STATUS_INACTIVE)));

        foreach ($vendorsCollection as $vendorsCollectionItem) {
            $activeVendorsToRecalculateBalance[] = $vendorsCollectionItem->getVendorId();
        }
        //--unset not active vendors


        foreach ($balancesByVendor as $vendor => $data) {
            $i = 0;

            foreach ($data as $month => $monthData) {
                if ($i == 0) {
                    $balancesByVendor[$vendor][$month]["balance_cumulative"] = $monthData["balance_per_month"];
                } else {
                    $balancesByVendor[$vendor][$month]["balance_cumulative"] = $monthData["balance_per_month"] + array_values($balancesByVendor[$vendor])[$i - 1]["balance_cumulative"];
                }
                $i++;
            }
        }


        //Recover empty rows if balance_cumulative not zero
        $startMonths = array();
        if (!empty($balancesByVendor)) {
            foreach ($balancesByVendor as $vendorId => $balancesByVendorItem) {
                $startMonths[$vendorId][array_keys($balancesByVendorItem)[0]] = $balancesByVendorItem[array_keys($balancesByVendorItem)[0]]["balance_cumulative"];
            }
        }
        unset($vendorId);

        $balancePeriods = Mage::helper("ghstatements/vendor_balance")->constructBalancePeriods($startMonths);

        $toUpdate = array();
        foreach ($balancePeriods as $vendorId => $balancePeriod) {

            foreach (array_keys($balancePeriod) as $balancePeriodDate) {

                $monthBefore = Mage::getModel('core/date')->date('Y-m', strtotime("-1 month", strtotime($balancePeriodDate)));

                if (isset($balancesByVendor[$vendorId][$balancePeriodDate])) {
                    $toUpdate[$vendorId][$balancePeriodDate] = sprintf("%.4f", $balancesByVendor[$vendorId][$balancePeriodDate]["balance_cumulative"]);
                } else {

                    //Build empty rows
                    if (in_array($vendorId, $activeVendorsToRecalculateBalance)) {
                        if (isset($balancesByVendor[$vendorId][$monthBefore])) {
                            $toUpdate[$vendorId][$balancePeriodDate] = sprintf("%.4f", $balancesByVendor[$vendorId][$monthBefore]["balance_cumulative"]);
                        } else {
                            $toUpdate[$vendorId][$balancePeriodDate] = sprintf("%.4f", $toUpdate[$vendorId][$monthBefore]);
                        }
                    }
                }
            }
        }

        unset($vendor);
        unset($month);


        $adapter = Mage::getSingleton('core/resource')->getConnection('core_write');
        foreach ($toUpdate as $vendor => $toUpdateData) {
            foreach ($toUpdateData as $month => $amount) {
                $adapter->insertOnDuplicate(
                    'gh_vendor_balance',
                    array("vendor_id" => $vendor, "date" => $month, "balance_cumulative" => $amount),
                    array('vendor_id', "date", "balance_cumulative")
                );
            }
        }

        //--Recover empty rows if balance_cumulative not zero
    }


    /**
     * Delete empty lines for not active vendors
     *
     * @throws Exception
     */
    public function removeInactiveVendorEmptyLines()
    {

        //fetch not active vendors
        $notActiveVendors = array();
        $vendorsCollection = Mage::getModel("udropship/vendor")->getCollection();
        $vendorsCollection->addFieldToFilter("status", array("neq" => array(ZolagoOs_OmniChannel_Model_Source::VENDOR_STATUS_ACTIVE)));

        if ($vendorsCollection->getSize() == 0)
            return;

        foreach ($vendorsCollection as $vendorsCollectionItem) {
            $notActiveVendors[] = $vendorsCollectionItem->getVendorId();
        }
        //--fetch not active vendors


        $balances = Mage::getModel("ghstatements/vendor_balance")
            ->getCollection()
            ->addFieldToFilter("status", GH_Statements_Model_Vendor_Balance::GH_VENDOR_BALANCE_STATUS_OPENED)
            ->addFieldToFilter("payment_from_client", "0.0000")
            ->addFieldToFilter("payment_return_to_client", "0.0000")
            ->addFieldToFilter("vendor_payment_cost", "0.0000")
            ->addFieldToFilter("vendor_invoice_cost", "0.0000")
            ->addFieldToFilter("balance_per_month", "0.0000")
            ->addFieldToFilter("balance_cumulative", "0.0000")
            ->addFieldToFilter("balance_due", "0.0000")
            ->addFieldToFilter("vendor_id", array("in" => $notActiveVendors));

        if ($balances->getSize() == 0)
            return;

        foreach ($balances as $balance) {
            $balance->delete();
        }
    }

}