<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
	$installer->getTable('salesrule/rule'), 
	'campaign_id',
	array(
		"type"		=> Varien_Db_Ddl_Table::TYPE_INTEGER,
		"nullable"  => true,
		"comment"	=> "Campaign ID",
		"default"	=> null,
	)
);


// Memory table for processing products in campaigns from coupons rules
$table = $installer->getConnection()
    ->newTable($installer->getTable('zolagosalesrule/product_in_campaign_tmp'))
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Product ID')
    ->addColumn('campaign_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Title')
    ->addIndex($installer->getIdxName($installer->getTable('zolagosalesrule/product_in_campaign_tmp'),
        array("product_id")),array("product_id"))
    ->addIndex($installer->getIdxName($installer->getTable('zolagosalesrule/product_in_campaign_tmp'),
        array("campaign_id")),array("campaign_id"))
    ->setOption("type", Varien_Db_Adapter_Pdo_Mysql::ENGINE_MEMORY);

$installer->getConnection()->createTable($table);

$installer->endSetup();
