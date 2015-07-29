<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * update refunds statements
 */
$table = $this->getTable('ghstatements/calendar_item');
$installer->getConnection()
	->modifyColumn(
		$table,
		"event_date",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_DATE,
			'nullable'  => false,
			'comment'   => 'Creation Time'
		)
	);

$installer->endSetup();
