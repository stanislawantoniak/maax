<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * update refunds statements
 */
$table = $this->getTable('ghstatements/refund');
$installer->getConnection()
	->modifyColumn(
		$table,
		"statement_id",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
			'nullable'  => true,
			'comment'   => 'Statement Id'
		)
	)
	->modifyColumn(
		$table,
		"operator_id",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
			'nullable'  => true,
			'comment'   => 'Operator Id'
		)
	)
	->addColumn(
		$table,
		"operator_name",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
			'length'    => 255,
			'nullable'  => true,
			'comment'   => 'Operator name'
		)
	);

$installer->endSetup();
