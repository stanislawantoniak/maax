<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * update order and rma track table for statements
 */

$installer->getConnection()
	->modifyColumn(
		$this->getTable("ghstatements/refund"),
		"date",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_DATE,
			'nullable'  => false,
			'comment'   => 'Refund date'
		)
	);


$installer->endSetup();
