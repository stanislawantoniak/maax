<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Basic structure of calendar
 */
$table = $installer->getConnection()
	->newTable($installer->getTable('ghbeacon/data'))
	->addColumn("id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity' => true,
		'nullable' => false,
		'primary' => true,
	))
	->addColumn("beacon_id", Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
		'nullable'  => false,
	))
	->addColumn("email", Varien_Db_Ddl_Table::TYPE_VARCHAR, 128, array(
		'nullable' => false,
	))
	->addColumn("distance", Varien_Db_Ddl_Table::TYPE_DOUBLE, null, array(
		'nullable' => false,
	))
	->addColumn("date", Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
		'nullable' => false,
	));

$installer->getConnection()->createTable($table);

$installer->endSetup();
