<?php
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

/*
 * Add Category Attributes related to Filters
 */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute(
    'catalog_category', Zolago_Solrsearch_Helper_Data::ZOLAGO_USE_IN_SEARCH_CONTEXT,
    array(
         'group'            => 'General Information',
         'input'            => 'select',
         'type'             => 'int',
         'label'            => 'Use in search context',
         'source'           => 'eav/entity_attribute_source_boolean',
         'backend'          => '',
         'visible'          => true,
         'required'         => false,
         'visible_on_front' => true,
         'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
         'user_defined'     => true,
         'default'          => 0,
         'position'         => 120
    )
);



/**
 * Product queue
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('zolagosolrsearch/queue_item'))
    ->addColumn('queue_id', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array( 
            'identity'  => true,
            'nullable'  => false,
            'primary'   => true,
    ))    
    ->addColumn('product_id',Varien_Db_Ddl_Table::TYPE_INTEGER, null, array (
        'nullable' => false,
    ))
    ->addColumn('store_id',Varien_Db_Ddl_Table::TYPE_INTEGER, null, array (
        'nullable' => false,
    ))
    ->addColumn('status',Varien_Db_Ddl_Table::TYPE_TEXT, 20, array (
        'nullable' => false,
    ))
    ->addColumn('core_name',Varien_Db_Ddl_Table::TYPE_TEXT, 50, array (
        'nullable' => false,
    ))
    ->addColumn('processed_at',Varien_Db_Ddl_Table::TYPE_DATETIME,null, array (
        'nullable' => true,
    ))
    ->addColumn('created_at',Varien_Db_Ddl_Table::TYPE_DATETIME,null, array (
        'nullable' => false,
    ))
    ->addIndex($installer->getIdxName('zolagosolrsearch/queue_item', array ('status')), array('status'))
    ->addIndex($installer->getIdxName('zolagosolrsearch/queue_item', array ('product_id')), array('product_id'))
    ->addIndex($installer->getIdxName('zolagosolrsearch/queue_item', array ('store_id')), array('store_id'))
    ->addIndex($installer->getIdxName('zolagosolrsearch/queue_item', array ('created_at')), array('created_at'))
	// Uniue key - do not double scopes and products
    /*->addIndex(
			$installer->getIdxName('zolagosolrsearch/queue_item', array ('product_id', 'store_id', 'status')), 
			array ('product_id', 'store_id', 'status'), 
			array("type"=>  Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))*/
    ->addForeignKey(
        $installer->getFkName('zolagosolrsearch/queue_item', 'product_id', 'catalog/product', 'entity_id'),
        'product_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('zolagosolrsearch/queue_item', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

$installer->getConnection()->createTable($table);    

$installer->endSetup();