<?php
/**
 * fakturowanie usług: prowizje i transport
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
$tableVendorPayment = $installer->getTable('zolagopayment/vendor_invoice');

$table = $connection->newTable($tableVendorPayment)
    ->addColumn('vendor_invoice_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        'unsigned' => true,
    ))
    ->addColumn('date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(), 'Vendor Invoice Date')
    ->addColumn('sale_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(), 'Sale Date')
    /* udropship_vendor.vendor_id */
    ->addColumn('vendor_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned' => true,
        'nullable' => false
    ))
    //WFIRMA fields
    ->addColumn('wfirma_invoice_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'unsigned' => true,
        'nullable' => false
    ))
    ->addColumn('wfirma_invoice_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, 15, array(
        'nullable' => false,
    ))
    //--WFIRMA fields

    //pola odpowiadające za prowizje/transport/marketing/inne
    ->addColumn('commission_netto', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable' => false,
    ))
    ->addColumn('commission_brutto', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable' => false,
    ))
    ->addColumn('transport_netto', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable' => false,
    ))
    ->addColumn('transport_brutto', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable' => false,
    ))
    ->addColumn('marketing_netto', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable' => false,
    ))
    ->addColumn('marketing_brutto', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable' => false,
    ))
    ->addColumn('other_netto', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable' => false,
    ))
    ->addColumn('other_brutto', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable' => false,
    ))
    //--pola odpowiadające za prowizje/transport/marketing/inne

    // Indexes
    ->addIndex($installer->getIdxName('udropship/vendor', array('vendor_id')),
        array('vendor_id'))
    // Foreign Keys
    ->addForeignKey(
        $installer->getFkName('zolagopayment/vendor_invoice', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
        Varien_Db_Ddl_Table::ACTION_RESTRICT, Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->getConnection()->createTable($table);

$installer->endSetup();