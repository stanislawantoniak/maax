<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


/**
 * Mapper main table
 */
 $table = $installer->getConnection()
    ->newTable($installer->getTable('zolagocampaign/campaign'))
    ->addColumn("campaign_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))    
    ->addColumn("vendor_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ))    
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
		'default'	=> 0
    ))
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 15, array(
        'nullable'  => false,
    ))
    ->addColumn('url_key', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        'nullable'  => false,
    ))
    ->addColumn('price_source_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ))
    ->addColumn('price_srp', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
        'nullable'  => false,
    ))
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        'nullable'  => false,
    ))
    ->addColumn('percent', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
		'default'	=> 0
    ))
    
	->addColumn('date_from', Varien_Db_Ddl_Table::TYPE_DATETIME, null)
	->addColumn('date_to', Varien_Db_Ddl_Table::TYPE_DATETIME, null)
	->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null)
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null)
    ->addIndex($installer->getIdxName('zolagocampaign/campaign', array('vendor_id')),
        array('vendor_id'))
    ->addForeignKey(
        $installer->getFkName('zolagocampaign/campaign', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
 
$installer->getConnection()->createTable($table);  


/**
 * Website - Campaign relation
 */

 $table = $installer->getConnection()
    ->newTable($installer->getTable('zolagocampaign/campaign_website'))
    ->addColumn("campaign_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'primary'   => true,
    ))    
    ->addColumn("website_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'primary'   => true,
    ))
    ->addIndex($installer->getIdxName('zolagocampaign/campaign_website', array('campaign_id')),
        array('campaign_id'))
    ->addIndex($installer->getIdxName('zolagocampaign/campaign_website', array('website_id')),
        array('website_id'))
    ->addForeignKey(
        $installer->getFkName('zolagocampaign/campaign_website', 'campaign_id', 'zolagocampaign/campaign', 'campaign_id'),
        'campaign_id', $installer->getTable('zolagocampaign/campaign'), 'campaign_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('zolagocampaign/campaign_website', 'website_id', 'core/website', 'website_id'),
        'website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
$installer->getConnection()->createTable($table);  

/**
 * Campaign - Product relation
 */

$table = $installer->getConnection()
    ->newTable($installer->getTable('zolagocampaign/campaign_product'))
    ->addColumn("campaign_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'primary'   => true,
    ))    
    ->addColumn("product_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'primary'   => true,
		'unsigned'  => true
    ))
    ->addColumn("position", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ))
    ->addIndex($installer->getIdxName('zolagocampaign/campaign_product', array('campaign_id')),
        array('campaign_id'))
    ->addIndex($installer->getIdxName('zolagocampaign/campaign_product', array('product_id')),
        array('product_id'))
    ->addForeignKey(
        $installer->getFkName('zolagocampaign/campaign_product', 'campaign_id', 'zolagocampaign/campaign', 'campaign_id'),
        'campaign_id', $installer->getTable('zolagocampaign/campaign'), 'campaign_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName('zolagocampaign/campaign_product', 'product_id', 'catalog/product', 'entity_id'),
        'product_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
$installer->getConnection()->createTable($table);  

$installer->endSetup();
