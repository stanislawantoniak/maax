<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$brandTable = $installer->getTable("zolagosizetable/vendor_brand");
$attributeTable = $installer->getTable("zolagosizetable/vendor_attribute_set");

/**
 * Brand access table
 */

$table = $installer->getConnection()
    ->newTable($brandTable)
    ->addColumn("vendor_brand_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))

    ->addColumn('vendor_id',    Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false, 'default'=>0))
    ->addColumn('brand_id',    Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false, 'default'=>0))

    ->addIndex($installer->getIdxName('zolagosizetable/vendor_brand', array('vendor_id')),
        array('vendor_id'))
    ->addIndex($installer->getIdxName('zolagosizetable/vendor_brand', array('brand_id')),
        array('brand_id'))


    ->addForeignKey(
        $installer->getFkName('zolagosizetable/vendor_brand', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship_vendor'), 'vendor_id',
         Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('zolagosizetable/vendor_brand', 'brand_id', 'eav/attribute_option', 'option_id'),
        'brand_id', $installer->getTable('eav/attribute_option'), 'option_id',
         Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
;
$installer->getConnection()->createTable($table);

/**
 * Attribute set access table
 */

$table = $installer->getConnection()
    ->newTable($attributeTable)
    ->addColumn("vendor_attribute_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))

    // Struct
    ->addColumn("attribute_set_id",       Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false))
    ->addColumn("vendor_id",    Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false))

    // Indexes
    ->addIndex($installer->getIdxName('zolagosizetable/vendor_attribute_set', array('vendor_id')),
        array('vendor_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    ->addIndex($installer->getIdxName('zolagosizetable/vendor_attribute_set', array('attribute_set_id')),
        array('attribute_set_id'))


    ->addForeignKey(
        $installer->getFkName('zolagosizetable/vendor_attribute_set', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship_vendor'), 'vendor_id',
         Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('zolagosizetable/vendor_attribute_set', 'attribute_set_id', 'eav/attribute_set', 'attribute_set_id'),
        'attribute_set_id', $installer->getTable('eav/attribute_set'), 'attribute_set_id',
         Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
;
$installer->getConnection()->createTable($table);

$installer->endSetup();
