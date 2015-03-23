<?php

/**
 * gh_api_message table
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$messageTable = $installer->getConnection()
	->newTable($installer->getTable('ghapi/message'))
	->addColumn('message_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,
		'nullable'  => false,
		'primary'   => true,
		'unsigned'  => true,
	))

	/* udropship_vendor.vendor_id */
	->addColumn('vendor_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
		'unsigned'  => true,
		'nullable'  => false
	))

	/* udropship_po.increment_id */
	->addColumn('po_increment_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
		'nullable'  => false
	))

	->addColumn('message', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
		'nullable'  => false
	))

	->addColumn('status', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
		'nullable'  => false,
		'default' => 0
	))

	->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		'nullable'  => false
	))

	// Indexes
	->addIndex($installer->getIdxName('udropship/vendor', array('vendor_id')),
		array('vendor_id'))

	// Foreign Keys
	->addForeignKey(
		$installer->getFkName('ghapi/message', 'vendor_id', 'udropship/vendor', 'vendor_id'),
		'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
	);

$installer->getConnection()->createTable($messageTable);

$installer->endSetup();

