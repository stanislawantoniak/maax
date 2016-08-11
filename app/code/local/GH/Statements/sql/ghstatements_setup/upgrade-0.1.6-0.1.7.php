<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Saldo sprzedawcy
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('ghstatements/vendor_balance'))
    ->addColumn("id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
    ), 'vendor balance id')
    ->addColumn("status", Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false
    ), 'Status miesiąca')
    ->addColumn('date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        'nullable' => false,
    ), 'Statement month (Miesiąc rozliczeniowy)')
    /* udropship_vendor.vendor_id */
    ->addColumn('vendor_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned' => true,
        'nullable' => false
    ))
    ->addColumn("payment_to_client", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable' => false
    ), 'Płatności od klientów')
    ->addColumn("payment_return_to_client", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable' => false
    ), 'Zwroty płatności do klientów')
    ->addColumn("vendor_payment_cost", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable' => false
    ), 'Wypłaty')
    ->addColumn("vendor_invoice_cost", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable' => false
    ), 'Faktury i korekty faktur')
    //Saldo
    ->addColumn("balance_per_month", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable' => false
    ), 'Bilans miesiąca')
    ->addColumn("balance_cumulative", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable' => false
    ), 'Saldo narastająco')
    ->addColumn("balance_due", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable' => false
    ), 'Saldo wymagalne')
    // Indexes
    ->addIndex($installer->getIdxName('udropship/vendor', array('vendor_id')),
        array('vendor_id'))
    // Foreign Keys
    ->addForeignKey(
        $installer->getFkName('ghstatements/vendor_balance', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
        Varien_Db_Ddl_Table::ACTION_RESTRICT, Varien_Db_Ddl_Table::ACTION_RESTRICT
    );

$installer->getConnection()->createTable($table);


$installer->endSetup();
