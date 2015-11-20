<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $this->getTable('ghstatements/track');

$installer->getConnection()
	->addColumn(
		$table,
		"title",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
			'comment'   => 'Carrier name',
			'nullable'  => true,
			'default'   => null
		)
	);

$installer->getConnection()
	->addColumn(
		$table,
		"customer_id",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
			'comment'   => 'Customer Id',
			'nullable'  => false,
			'default'   => 0
		)
	);


$installer->getConnection()
	->addColumn(
		$this->getTable("ghstatements/statement"),
		"statement_pdf",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
			'comment'   => 'Statement Pdf path',
			'nullable'  => true,
			'default'   => null
		)
	);

$installer->endSetup();
