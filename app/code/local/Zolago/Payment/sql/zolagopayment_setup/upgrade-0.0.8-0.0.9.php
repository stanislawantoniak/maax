<?php

/**
 * zolago payment allocations table
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$allocationTable = $installer->getConnection()
    ->newTable($installer->getTable('zolagopayment/allocation'))
    ->addColumn('allocation_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true
    ))

    /* sales_payment_transaction.transaction_id */
    ->addColumn('transaction_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned'  => true,
        'nullable'  => false
    ))

    /* udropship_po.entity_id */
    ->addColumn('po_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned'  => true,
        'nullable'  => false
    ))

    /* udropship_vendor.vendor_id -> vendor_id can be taken from zolago_operator */
//    ->addColumn('vendor_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
//        'unsigned'  => true,
//        'nullable'  => false
//    ))

    ->addColumn('allocation_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false
    ))

    ->addColumn('allocation_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 15, array(
        'nullable'  => false
    ))

    /* zolago_operator.operator_id */
    ->addColumn('operator_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'nullable'  => false
    ))

    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false
    ))

    ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array())

    // Indexes
    ->addIndex($installer->getIdxName('sales/payment_transaction', array('transaction_id')),
        array('transaction_id'))
    ->addIndex($installer->getIdxName('udpo/po', array('entity_id')),
        array('po_id'))

    // Foreign Keys
//    transaction_id
    ->addForeignKey(
        $installer->getFkName('zolagopayment/allocation', 'transaction_id', 'sales/payment_transaction', 'transaction_id'),
        'transaction_id', $installer->getTable('sales/payment_transaction'), 'transaction_id'
    )
//    po_id
    ->addForeignKey(
        $installer->getFkName('zolagopayment/allocation', 'po_id', 'udpo/po', 'entity_id'),
        'po_id', $installer->getTable('udpo/po'), 'entity_id'
    )

//    operator_id
    ->addForeignKey(
        $installer->getFkName('zolagopayment/allocation', 'operator_id', 'zolagooperator/operator', 'operator_id'),
        'operator_id', $installer->getTable('zolagooperator/operator'), 'operator_id'
    );


$installer->getConnection()->createTable($allocationTable);

/**
 * sales_payment_transaction update
 */

$installer->getConnection()
    ->addColumn($installer->getTable('sales/payment_transaction'), "transaction_amount", array(
        "type"		=> Varien_Db_Ddl_Table::TYPE_DECIMAL,
        "length"	=> "12,4",
        'nullable'	=> false,
        'comment'   => 'Transaction amount'
    ));

$installer->getConnection()
    /* zolago_operator.operator_id */
    ->addColumn($installer->getTable('sales/payment_transaction'), "operator_id", array(
        "type"      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        "length"    => 11,
        'comment'   => 'Operator ID'
    ));

$installer->endSetup();

