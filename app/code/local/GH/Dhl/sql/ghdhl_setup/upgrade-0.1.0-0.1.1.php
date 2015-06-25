<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$dhDHLVendorTable = $installer->getTable("ghdhl/dhl_vendor");
/**
 * DHL Account Access table
 */

$table = $installer->getConnection()
    ->newTable($dhDHLVendorTable)
    ->addColumn('vendor_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false, 'default' => 0))
    ->addColumn('dhl_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false, 'default' => 0))

    ->addIndex(
        $installer->getIdxName('ghdhl/dhl', array('dhl_id')),
        array('dhl_id')
    )
    ->addForeignKey(
        $installer->getFkName('ghdhl/dhl_vendor', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship_vendor'), 'vendor_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('ghdhl/dhl_vendor', 'dhl_id', 'ghdhl/dhl', 'id'),
        'dhl_id', $installer->getTable('ghdhl/dhl'), 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

$installer->getConnection()->createTable($table);

$installer->endSetup();
