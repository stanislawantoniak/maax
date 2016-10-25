<?php
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$tableName = $installer->getTable('zolagocatalog/external_stock');

$fields = array('external_sku', 'external_stock_id', 'vendor_id');
$indexType = Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE;
$indexName = $installer->getIdxName($tableName, $fields, $indexType);

$table = $installer->getConnection()
    ->newTable($installer->getTable('zolagocatalog/external_stock'))
    ->addColumn(
        'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'identity' => true,
            'nullable' => false,
            'primary' => true,
        )
    )
    ->addColumn('external_sku',
        Varien_Db_Ddl_Table::TYPE_TEXT, 64,
        array('nullable' => false), 'External Product SKU'
    )
    ->addColumn('external_stock_id',
        Varien_Db_Ddl_Table::TYPE_TEXT, 64,
        array('nullable' => false), 'External Stock ID'
    )
    ->addColumn('vendor_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned' => true,
        'nullable' => false
    ))
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable' => false,
        'default' => '0.0000',
    ), 'Qty')
    ->addColumn(
        'date_update', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
        array(
            'nullable' => false
        )
    )
    // Indexes
    ->addIndex($installer->getIdxName('udropship/vendor', array('vendor_id')),
        array('vendor_id'))
    // Foreign Keys
    ->addForeignKey(
        $installer->getFkName('zolagocatalog/external_stock', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
        Varien_Db_Ddl_Table::ACTION_NO_ACTION,
        Varien_Db_Ddl_Table::ACTION_NO_ACTION
    );


$installer->getConnection()->createTable($table);

$installer->getConnection()->addIndex(
    $tableName,
    $indexName,
    $fields,
    $indexType
);

$installer->endSetup();