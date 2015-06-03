<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$tableName = $installer->getTable('ghrewrite/url');

$installer->getConnection()->addColumn($tableName, 'store_id', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_SMALLINT,
	"length"		=> "5",
	"comment"	=> "Store Id",
	"default" 	=> 0,
	"nullable" 	=> false,
));

$installer->endSetup();