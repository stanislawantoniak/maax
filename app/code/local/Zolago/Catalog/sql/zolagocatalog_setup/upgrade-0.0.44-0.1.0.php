<?php
/**
 * @var $installer Mage_Catalog_Model_Resource_Setup
 */
$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');


$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('zolagocatalog/description_history'))
    ->addColumn(
        'history_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'identity' => true,
            'nullable' => false,
            'primary' => true,
        )
    )
    ->addColumn(
        'changes_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
        array(
            'nullable' => false,
        )
    )
    ->addColumn(
        'changes_data',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        null,
        array('nullable' => false),
        'Changes data'
    );
$installer->getConnection()->createTable($table);

$installer->endSetup();