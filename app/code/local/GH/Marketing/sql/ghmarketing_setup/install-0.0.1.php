<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('ghmarketing/marketing_cost_type'))
    ->addColumn("marketing_cost_type_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
    ))
    ->addColumn("code", Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable' => false,
    ))
    ->addColumn("name", Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
        'nullable' => false,
    ));
        
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('ghmarketing/marketing_cost'))
    ->addColumn("marketing_cost_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
    ))
    ->addColumn("vendor_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ))
    ->addColumn("product_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ))
    ->addColumn("date", Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => false,
    ))
    ->addColumn('type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ))
    ->addColumn('cost', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
        'nullable' => false,
    ))
    ->addColumn('click_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ))
    ->addColumn('statement', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ))

    ->addIndex($installer->getIdxName('ghmarketing/marketing_cost', array('vendor_id')),
        array('vendor_id'))
    ->addIndex($installer->getIdxName('ghmarketing/marketing_cost', array('product_id')),
        array('product_id'))
    ->addIndex($installer->getIdxName('ghmarketing/marketing_cost', array('type_id')),
        array('type_id'))
    ->addForeignKey(
        $installer->getFkName('ghmarketing/marketing_cost', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('ghmarketing/marketing_cost', 'product_id', 'catalog/product', 'entity_id'),
        'product_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('ghmarketing/marketing_cost', 'type_id', 'ghmarketing/marketing_cost_type', 'marketing_cost_type_id'),
        'type_id', $installer->getTable('ghmarketing/marketing_cost_type'), 'marketing_cost_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

$installer->getConnection()->createTable($table);

$installer->endSetup();

