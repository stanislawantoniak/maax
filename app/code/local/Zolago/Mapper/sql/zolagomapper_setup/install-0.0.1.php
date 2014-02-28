<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


/**
 * Mapper main table
 */
 $table = $installer->getConnection()
    ->newTable($installer->getTable('zolagomapper/mapper'))
    ->addColumn("mapper_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))    
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ))
    ->addColumn('attribute_set_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null)
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
		'default'	=> 0
    ))
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        'nullable'  => false,
    ))
    ->addColumn('priority', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
    ))
    ->addColumn('conditions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
    ))
	->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null)
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null)
    ->addIndex($installer->getIdxName('zolagomapper/mapper', array('attribute_set_id')),
        array('attribute_set_id'))
    ->addIndex($installer->getIdxName('zolagomapper/mapper', array('website_id')),
        array('website_id'))
    ->addForeignKey(
        $installer->getFkName('zolagomapper/mapper', 'attribute_set_id', 'eav/attribute_set', 'attribute_set_id'),
        'attribute_set_id', $installer->getTable('eav/attribute_set'), 'attribute_set_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('zolagomapper/mapper', 'website_id', 'core/website', 'website_id'),
        'website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
$installer->getConnection()->createTable($table);  

/**
 * Mapper - category relations
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('zolagomapper/mapper_category'))
    ->addColumn("mapper_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ))    
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ))
    ->addIndex($installer->getIdxName('zolagomapper/mapper_category', array('mapper_id', 'category_id')),
        array('mapper_id', 'category_id'), array("type"=>Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY))
    ->addIndex($installer->getIdxName('zolagomapper/mapper_category', array('mapper_id')),
        array('mapper_id'))
    ->addIndex($installer->getIdxName('zolagomapper/mapper_category', array('category_id')),
        array('category_id'))
    ->addForeignKey(
        $installer->getFkName('zolagomapper/mapper_category', 'mapper_id', 'zolagomapper/mapper', 'mapper_id'),
        'mapper_id', $installer->getTable('zolagomapper/mapper'), 'mapper_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('zolagomapper/mapper_category', 'category_id', 'catalog/category', 'entity_id'),
        'category_id', $installer->getTable('catalog/category'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
$installer->getConnection()->createTable($table);  

/**
 * Mapper queue
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('zolagomapper/mapper_queue_mapper'))
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
    ->addColumn('mapper_id',Varien_Db_Ddl_Table::TYPE_INTEGER, null, array (
        'nullable' => false,
    ))
    ->addIndex($installer->getIdxName('zolagomapper/mapper_queue_mapper', array ('insert_date')), array('insert_date'))
    ->addIndex($installer->getIdxName('zolagomapper/mapper_queue_mapper', array ('status')), array('status'))
    ->addIndex($installer->getIdxName('zolagomapper/mapper_queue_mapper', array ('mapper_id')), array('mapper_id'))
    ->addForeignKey(
        $installer->getFkName('zolagomapper/mapper_queue_mapper', 'mapper_id', 'zolagomapper/mapper', 'mapper_id'),
        'mapper_id', $installer->getTable('zolagomapper/mapper'), 'mapper_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

$installer->getConnection()->createTable($table);  
/**
 * Map product queue
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('zolagomapper/mapper_queue_product'))
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
    ->addIndex($installer->getIdxName('zolagomapper/mapper_queue_product', array ('insert_date')), array('insert_date'))
    ->addIndex($installer->getIdxName('zolagomapper/mapper_queue_product', array ('status')), array('status'))
    ->addIndex($installer->getIdxName('zolagomapper/mapper_queue_product', array ('product_id')), array('product_id'))
    ->addForeignKey(
        $installer->getFkName('zolagomapper/mapper_queue_product', 'product_id', 'catalog/product', 'entity_id'),
        'product_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

$installer->getConnection()->createTable($table);  
                                

$installer->endSetup();
