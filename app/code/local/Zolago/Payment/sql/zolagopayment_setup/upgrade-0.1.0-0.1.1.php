<?php
/**
 * wypłaty do sprzedawców
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
$tableVendorPayment = $installer->getTable('zolagopayment/vendor_payment');

$table = $connection->newTable($tableVendorPayment)
    ->addColumn('vendor_payment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        'unsigned' => true,
    ))
    ->addColumn('date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(), 'Vendor Payment Date')
    ->addColumn('cost', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
        'nullable' => false,
    ))
    /* udropship_vendor.vendor_id */
    ->addColumn('vendor_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned' => true,
        'nullable' => false
    ))
    ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(), 'Comment')
    // Indexes
    ->addIndex($installer->getIdxName('udropship/vendor', array('vendor_id')),
        array('vendor_id'))
    // Foreign Keys
    ->addForeignKey(
        $installer->getFkName('zolagopayment/vendor_payment', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->getConnection()->createTable($table);

$installer->endSetup();