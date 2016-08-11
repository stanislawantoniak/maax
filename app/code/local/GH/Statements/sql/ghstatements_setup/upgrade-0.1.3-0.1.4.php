<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();


/**
 * Structure of gh_statements_rma
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('ghstatements/marketing'))

    ->addColumn("id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'marketing statement id')

    ->addColumn('statement_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'statement id')

    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'catalog_product_entity entity_id')

    ->addColumn('product_sku', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
        'nullable'  => false,
    ), 'catalog_product_entity sku')

    ->addColumn('product_vendor_sku', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
        'nullable'  => false,
    ), 'product vendor sku')

    ->addColumn('product_name', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
    ), 'product name')

    ->addColumn('marketing_cost_type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'marketing cost type id')

    ->addColumn('marketing_cost_type_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
        'nullable'  => false,
    ), 'product name')

    ->addColumn('date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        'nullable'  => false,
    ), 'date')

    ->addColumn("value", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
        'nullable'  => false
    ),'gh_marketing_cost cost + marketing comission');

$installer->getConnection()->createTable($table);

$installer->getConnection()
    ->addForeignKey(
        $installer->getFkName('ghstatements/marketing', 'statement_id', 'ghstatements/statement', 'id'), //$fkName
        $installer->getTable('ghstatements/marketing'), //$tableName
        'statement_id', //$columnName
        $installer->getTable('ghstatements/statement'), //$refTableName
        'id', //$refColumnName
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->getConnection()
    ->addColumn(
        $this->getTable('ghstatements/statement'),
        "marketing_value",
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable'  => false,
            'comment'   => 'Marketing Value',
            'length'      => '12,4'
        )
    );

$installer->endSetup();
