<?php

/**
 * DHL Account Access table
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$dhDHLVendorTable = $installer->getTable("ghdhl/dhl_vendor");
$installer->run("
DROP TABLE IF EXISTS {$dhDHLVendorTable};
");

/**
 * DHL Account Access table
 */

$table = $installer->getConnection()
    ->newTable($dhDHLVendorTable)
    ->addColumn("id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
    ))
    ->addColumn('vendor_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false, 'default' => 0))
    ->addColumn('dhl_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false, 'default' => 0))
    ->addIndex(
        $installer->getIdxName('ghdhl/dhl', array('dhl_id')),
        array('dhl_id')
    )
;

$installer->getConnection()->createTable($table);

$installer->endSetup();
