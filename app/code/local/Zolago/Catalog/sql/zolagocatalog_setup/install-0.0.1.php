<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Category filter table
 */
 $table = $installer->getConnection()
    ->newTable($installer->getTable('zolagocatalog/category_filter'))
    ->addColumn("filter_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    )) 
    ->addColumn('parent_filter_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_INTEGER)    
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER)    
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array("nullable"=>false, "default"=>0))
    ->addColumn('show_multiple', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array("nullable"=>false, "default"=>0))
    ->addColumn('use_specified_options', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array("nullable"=>false, "default"=>0))    
    ->addColumn('specified_options', Varien_Db_Ddl_Table::TYPE_TEXT, 1024*4 /*4kb field */)
    ->addColumn('frontend_renderer', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
		 
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Creation Time')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Update Time')
    ->addIndex($installer->getIdxName('zolagocatalog/category_filter', array('parent_filter_id')),
        array('parent_filter_id'))
    ->addIndex($installer->getIdxName('zolagocatalog/category_filter', array('attribute_id')),
        array('attribute_id'))
    ->addIndex($installer->getIdxName('zolagocatalog/category_filter', array('category_id')),
        array('category_id'))
    ->addForeignKey(
        $installer->getFkName('zolagocatalog/category_filter', 'parent_filter_id', 'zolagocatalog/category_filter','filter_id'),
        'parent_filter_id', $installer->getTable('zolagocatalog/category_filter'), 'filter_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('zolagocatalog/category_filter', 'attribute_id', 'eav/attribute','attribute_id'),
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('zolagocatalog/category_filter', 'category_id', 'catalog/category','entity_id'),
        'category_id', $installer->getTable('catalog/category'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
 
$installer->getConnection()->createTable($table);
$installer->endSetup();
