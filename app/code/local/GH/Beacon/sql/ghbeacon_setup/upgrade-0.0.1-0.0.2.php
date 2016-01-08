<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
	->addColumn(
		$this->getTable('ghbeacon/data'),
		"event_type",
		array(
			"type"      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
			"nullable"  => false,
			"default"   => 0,
			"comment"   => "Event type"
		)
	);

$installer->getConnection()
	->addColumn(
		$this->getTable('zolagopos/pos'),
		"beacon_id",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
			'nullable'  => true,
			'length'    => 64,
			"comment"   => "Beacon ID",
			"default"   => null
		)
	);

$installer->getConnection()
	->addColumn(
		$this->getTable('zolagopos/pos'),
		"beacon_name",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
			'nullable'  => true,
			'length'    => 128,
			"comment"   => "Internal beacon name",
			"default"   => null
		)
	);

$installer->endSetup();

