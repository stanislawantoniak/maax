<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$posVendorWebsiteTable = $installer->getTable("zolagopos/pos_vendor_website");


/**
 * POS - Vendor - Website relation
 */

$table = $installer->getConnection()
    ->newTable($posVendorWebsiteTable)
    // Structure
    ->addColumn("pos_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false))
    ->addColumn("vendor_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false))
    ->addColumn("website_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false))
    // Indexes
    ->addIndex($installer->getIdxName('zolagopos/pos_vendor_website', array('pos_id', 'vendor_id', 'website_id')),
        array('pos_id', 'vendor_id', 'website_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    ->addIndex($installer->getIdxName('zolagopos/pos_vendor_website', array('pos_id')),
        array('pos_id'))
    ->addIndex($installer->getIdxName('zolagopos/pos_vendor_website', array('vendor_id')),
        array('vendor_id'))
    ->addIndex($installer->getIdxName('zolagopos/pos_vendor_website', array('website_id')),
        array('website_id'))
    // Foreign Keys
    ->addForeignKey(
        $installer->getFkName('zolagopos/pos_vendor_website', 'pos_id', 'zolagopos/pos', 'pos_id'),
        'pos_id', $installer->getTable('zolagopos/pos'), 'pos_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('zolagopos/pos_vendor_website', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('zolagopos/pos_vendor_website', 'website_id', 'core/website', 'website_id'),
        'website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    );
$installer->getConnection()->createTable($table);

$installer->endSetup();
