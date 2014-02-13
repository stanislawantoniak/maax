<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$posTable = $installer->getTable("zolagopos/pos");
$posVendorTable = $installer->getTable("zolagopos/pos_vendor");

/**
 * POS table
 */

$table = $installer->getConnection()
    ->newTable($posTable)
    ->addColumn("pos_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))
        
    // Struct
    ->addColumn('external_id',      Varien_Db_Ddl_Table::TYPE_TEXT, 100)
    ->addColumn('is_active',        Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array('default'=>0, 'nullable' => false))
    ->addColumn('client_number',    Varien_Db_Ddl_Table::TYPE_TEXT, 100)
    ->addColumn('minimal_stock',    Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false, 'default'=>1))
        
    // Pos address
    ->addColumn("name",             Varien_Db_Ddl_Table::TYPE_TEXT, 100, array('nullable'  => false))
    ->addColumn('company',          Varien_Db_Ddl_Table::TYPE_TEXT, 150)
    ->addColumn("country_id",       Varien_Db_Ddl_Table::TYPE_TEXT, 2, array('nullable'  => false))
    ->addColumn("region_id",        Varien_Db_Ddl_Table::TYPE_INTEGER)
    ->addColumn("region",           Varien_Db_Ddl_Table::TYPE_TEXT, 100)
    ->addColumn('postcode',         Varien_Db_Ddl_Table::TYPE_TEXT, 20)
    ->addColumn('street',           Varien_Db_Ddl_Table::TYPE_TEXT, 150, array('nullable'  => false))
    ->addColumn('city',             Varien_Db_Ddl_Table::TYPE_TEXT, 100, array('nullable'  => false))
    ->addColumn('email',            Varien_Db_Ddl_Table::TYPE_TEXT, 100)
    ->addColumn('phone',            Varien_Db_Ddl_Table::TYPE_TEXT, 50, array('nullable'  => false))
    
    // Misc
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Creation Time')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Update Time')
        
    // Indexes
    ->addIndex($installer->getIdxName('zolagopos/pos', array('external_id')),
        array('external_id'))
    ->addIndex($installer->getIdxName('zolagopos/pos', array('client_number')),
        array('client_number'))
    ->addIndex($installer->getIdxName('zolagopos/pos', array('country_id')),
        array('country_id'))
    ->addIndex($installer->getIdxName('zolagopos/pos', array('region_id')),
        array('region_id'))
    
    // Foreign Keys
    ->addForeignKey(
        $installer->getFkName('zolagopos/pos', 'country_id', 'directory/country', 'country_id'),
        'country_id', $installer->getTable('directory/country'), 'country_id')
    ->addForeignKey(
        $installer->getFkName('zolagopos/pos', 'region_id', 'directory/country_region', 'region_id'),
        'region_id', $installer->getTable('directory/country_region'), 'region_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_NO_ACTION);

$installer->getConnection()->createTable($table);

/**
 * POS - Vendor relation
 */

$table = $installer->getConnection()
    ->newTable($posVendorTable)

    // Struct
    ->addColumn("pos_id",       Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false))
    ->addColumn("vendor_id",    Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false))
    ->addColumn('is_owner',     Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array('default'=>0, 'nullable' => false))
        
    // Indexes
    ->addIndex($installer->getIdxName('zolagopos/pos_vendor', array('pos_id', 'vendor_id')),
        array('pos_id', 'vendor_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    ->addIndex($installer->getIdxName('zolagopos/pos_vendor', array('pos_id')),
        array('pos_id'))
    ->addIndex($installer->getIdxName('zolagopos/pos_vendor', array('vendor_id')),
        array('vendor_id'))
    
    // Foreign Keys
    ->addForeignKey(
        $installer->getFkName('zolagopos/pos_vendor', 'pos_id', 'zolagopos/pos', 'pos_id'),
        'pos_id', $installer->getTable('zolagopos/pos'), 'pos_id', 
         Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
     )
     ->addForeignKey(
        $installer->getFkName('zolagopos/pos_vendor', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id', 
         Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
     );
$installer->getConnection()->createTable($table);

$installer->endSetup();

?>
