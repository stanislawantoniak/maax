<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();


/**
 * Structure of gh_statements_track
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('ghstatements/track'))

    ->addColumn("id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Id of statement track')

    ->addColumn('statement_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Statement id')

    ->addColumn('po_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Udropship_po entity_id')

    ->addColumn('po_increment_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        'nullable'  => false,
    ), 'Udropship_po increment_id')

    ->addColumn('rma_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => true,
    ), 'Urma_rma entity_id')

    ->addColumn('rma_increment_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        'nullable'  => true,
    ), 'Urma_rma increment_id')

    ->addColumn('shipped_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        'nullable'  => false,
    ), 'Shipped date')

    ->addColumn('track_number', Varien_Db_Ddl_Table::TYPE_TEXT, '64K', array(
        'nullable' => false
    ), 'Track number')

     ->addColumn("charge_shipment", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
         'nullable' => false,
     ), 'Shipment charge')

    ->addColumn("charge_fuel", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
         'nullable' => false,
     ), 'Fuel charge')

     ->addColumn("charge_insurance", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
         'nullable' => false,
     ), 'Insurance charge')

     ->addColumn("charge_cod", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
         'nullable' => false,
     ), 'COD charge')

    ->addColumn("charge_subtotal", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable' => false,
    ), 'Total netto shipment charge')

    ->addColumn("charge_total", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable' => false,
    ), 'Total brutto shipment charge');

$installer->getConnection()->createTable($table);

$installer->endSetup();
