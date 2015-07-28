<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * update order and rma track table for statements
 */

$installer->getConnection()
	->addColumn(
		$this->getTable("sales/shipment_track"),
		"statement_id",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
			'nullable'  => true,
			'comment'   => 'Statement Id'
		)
	);

$installer->getConnection()
	->addColumn(
		$this->getTable("urma/rma_track"),
		"statement_id",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
			'nullable'  => true,
			'comment'   => 'Statement Id'
		)
	);

$installer->endSetup();
