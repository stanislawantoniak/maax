<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();


$installer->getConnection()
	->addColumn(
		$this->getTable("ghstatements/statement"),
		"date_from",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
			'comment'   => 'statement date from',
			'nullable'  => true,
			'default'   => null
		)
	);

$installer->endSetup();
