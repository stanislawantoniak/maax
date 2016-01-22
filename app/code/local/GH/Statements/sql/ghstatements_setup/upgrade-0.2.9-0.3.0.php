<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$table = $this->getTable("ghstatements/refund");

$installer->getConnection()
	->addColumn(
		$table,
		"registered_value",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
			'nullable'  => false,
			'comment'   => 'Registered value',
			'default'   => 0.00,
			'length'    => '(12,4)'
		)
	);

$installer->getConnection()
	->modifyColumn(
		$table,
		"value",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
			'nullable'  => false,
			'comment'   => 'Value',
			'default'   => 0.00,
			'length'    => '(12,4)'
		)
	);

$installer->endSetup();
