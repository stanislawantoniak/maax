<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getConnection()
	->newTable($installer->getTable('modagointegrator/log'))
	->addColumn("id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,
		'nullable'  => false,
		'primary'   => true
	))
	->addColumn('text', Varien_Db_Ddl_Table::TYPE_TEXT, 1024, array(
		'nullable'  => false
	))
	->addColumn("date", Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
		'nullable'  => false,
	));

$installer->getConnection()->createTable($table);

$installer->endSetup();