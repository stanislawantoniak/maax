<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

/**
 * Product price type queue
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('zolagocatalog/queue_pricetype'))
    ->addColumn(
        'queue_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
             'identity' => true,
             'nullable' => false,
             'primary'  => true,
        )
    )
    ->addColumn(
        'insert_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
        array(
             'nullable' => false,
        )
    )
    ->addColumn(
        'process_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
        array(
             'nullable' => true,
        )
    )
    ->addColumn(
        'status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
        array(
             'nullable' => false,
             'default'  => 0,
        )
    )
    ->addColumn(
        'product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
             'nullable' => false,
        )
    )


    ->addIndex($installer->getIdxName('zolagocatalog/queue_pricetype', array('insert_date')), array('insert_date'))
    ->addIndex($installer->getIdxName('zolagocatalog/queue_pricetype', array('status')), array('status'))

    ->addIndex($installer->getIdxName('zolagocatalog/queue_pricetype', array('product_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('product_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))


    ->addForeignKey(
        $installer->getFkName('zolagocatalog/queue_pricetype', 'product_id', 'catalog/product', 'entity_id'),
        'product_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->getConnection()->createTable($table);

$installer->endSetup();