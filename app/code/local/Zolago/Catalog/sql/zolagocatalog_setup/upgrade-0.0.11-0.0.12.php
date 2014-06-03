<?php


$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Product configurable queue
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('zolagocatalog/queue_configurable'))
    ->addColumn('queue_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))
    ->addColumn('insert_date',Varien_Db_Ddl_Table::TYPE_TIMESTAMP,null, array (
        'nullable' => false,
    ))
    ->addColumn('process_date',Varien_Db_Ddl_Table::TYPE_TIMESTAMP,null, array (
        'nullable' => true,
    ))
    ->addColumn('status',Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array (
        'nullable' => false,
        'default' => 0,
    ))
    ->addColumn('product_id',Varien_Db_Ddl_Table::TYPE_INTEGER, null, array (
        'nullable' => false,
    ))
//    ->addColumn('website_id',Varien_Db_Ddl_Table::TYPE_INTEGER, null, array (
//        'nullable' => false,
//    ))
    ->addIndex($installer->getIdxName('zolagocatalog/queue_configurable', array ('insert_date')), array('insert_date'))
    ->addIndex($installer->getIdxName('zolagocatalog/queue_configurable', array ('status')), array('status'))
    ->addIndex($installer->getIdxName('zolagocatalog/queue_configurable', array ('product_id')), array('product_id'))
//    ->addIndex($installer->getIdxName('zolagocatalog/queue_configurable', array ('website_id')), array('website_id'))
    ->addForeignKey(
        $installer->getFkName('zolagocatalog/queue_configurable', 'product_id', 'catalog/product', 'entity_id'),
        'product_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
//    ->addForeignKey(
//        $installer->getFkName('zolagocatalog/queue_configurable', 'website_id', 'core/website', 'website_id'),
//        'website_id', $installer->getTable('core/website'), 'website_id',
//        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
;

$installer->getConnection()->createTable($table);

$installer->endSetup();