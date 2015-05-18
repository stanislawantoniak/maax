<?php
/**
 * url rewrites table installer
 */


$installer = $this;

$installer->startSetup();

 $table = $installer->getConnection()
    ->newTable($installer->getTable('ghrewrite/url'))
    ->addColumn("url_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    )) 
    ->addColumn('url_rewrite_id', Varien_Db_Ddl_Table::TYPE_INTEGER)
    ->addColumn('hash_id', Varien_Db_Ddl_Table::TYPE_TEXT,255)
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER)    
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT)    
    ->addColumn('meta_description', Varien_Db_Ddl_Table::TYPE_TEXT)    
    ->addColumn('meta_keywords', Varien_Db_Ddl_Table::TYPE_TEXT)    
    ->addColumn('category_name', Varien_Db_Ddl_Table::TYPE_TEXT)    
    ->addColumn('text_field_category', Varien_Db_Ddl_Table::TYPE_TEXT)    
    ->addColumn('text_field_filter', Varien_Db_Ddl_Table::TYPE_TEXT)    
    ->addColumn('listing_title', Varien_Db_Ddl_Table::TYPE_TEXT)    
    ->addColumn('url', Varien_Db_Ddl_Table::TYPE_TEXT,255)
    ->addColumn('filters', Varien_Db_Ddl_Table::TYPE_TEXT)    

    ->addIndex($installer->getIdxName('ghrewrite/url', array('url_rewrite_id')),
        array('url_rewrite_id'))
    ->addIndex($installer->getIdxName('ghrewrite/url', array('hash_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('hash_id'),array('type'=>Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('ghrewrite/url', array('url'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('url'),array('type'=>Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addForeignKey(
        $installer->getFkName('ghrewrite/url', 'url_rewrite_id', 'core/url_rewrite','url_rewrite_id'),
        'url_rewrite_id', $installer->getTable('core/url_rewrite'), 'url_rewrite_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE);


$installer->getConnection()->createTable($table);
$installer->endSetup();
