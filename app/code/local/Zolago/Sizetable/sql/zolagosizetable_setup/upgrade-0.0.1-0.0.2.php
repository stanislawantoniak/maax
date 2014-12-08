<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$sizeTable = $installer->getTable("zolagosizetable/sizetable");
$sizeTableScope = $installer->getTable("zolagosizetable/sizetable_scope");
$sizeTableRule = $installer->getTable("zolagosizetable/sizetable_rule");

/**
 * sizetable table
 */

$table = $installer->getConnection()
    ->newTable($sizeTable)
    ->addColumn("sizetable_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))

    ->addColumn('vendor_id',    Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false ), 'Vendor ID')
    ->addColumn('name',         Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array('nullable' => false ), 'Sizetable name')
    ->addColumn('default_value',      Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array('nullable' => false ), 'Sizetable default value')

    ->addForeignKey(
        $installer->getFkName('zolagosizetable/sizetable', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship_vendor'), 'vendor_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ;

$installer->getConnection()->createTable($table);

/**
 * sizetable scope table
 */

$table = $installer->getConnection()
    ->newTable($sizeTableScope)
    ->addColumn('sizetable_id', Varien_Db_Ddl_Table::TYPE_INTEGER,  null, array('nullable' => false), 'Sizetable ID')
    ->addColumn('store_id',     Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Store ID')
    ->addColumn('value',        Varien_Db_Ddl_Table::TYPE_VARCHAR,  null, array('nullable' => false ), 'Value for store')

    ->addIndex($installer->getIdxName('zolagosizetable/sizetable_scope', array('sizetable_id')),
        array('sizetable_id'))

    ->addForeignKey($installer->getFkName('zolagosizetable/sizetable_scope', 'sizetable_id', 'zolagosizetable/sizetable', 'sizetable_id'),
        'sizetable_id', $installer->getTable('zolagosizetable/sizetable'), 'sizetable_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
;

$installer->getConnection()->createTable($table);

/**
 * sizetable rule
 */

$table = $installer->getConnection()
    ->newTable($sizeTableRule)
    ->addColumn('rule_id',         Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true ), 'Rule ID')
    ->addColumn('sizetable_id',    Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false ), 'Sizetable ID')
    ->addColumn('vendor_id',       Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false ), 'Vendor ID')
    ->addColumn("brand_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable'  => false ), 'Brand ID')

    //sizetable
    ->addForeignKey(
        $installer->getFkName('zolagosizetable/sizetable_rule', 'sizetable_id', 'zolagosizetable/sizetable', 'sizetable_id'),
        'sizetable_id', $installer->getTable('zolagosizetable/sizetable'), 'sizetable_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

    //vendor
    ->addForeignKey(
        $installer->getFkName('zolagosizetable/sizetable_rule', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship_vendor'), 'vendor_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

    //brand
    ->addForeignKey(
        $installer->getFkName('zolagosizetable/sizetable_rule', 'brand_id', 'zolagosizetable/vendor_brand', 'brand_id'),
        'brand_id', $installer->getTable('zolagosizetable/vendor_brand'), 'brand_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ;

$installer->getConnection()->createTable($table);

$installer->endSetup();
