<?php

/**
 * gh_api_user table
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$userTable = $installer->getConnection()
	->newTable($installer->getTable('ghapi/user'))
	->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
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

	/* sha256 */
	->addColumn('api_key', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
		'nullable'  => false
	))

	/* sha256 */
	->addColumn('password', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
		'nullable'  => false
	))

	// Indexes
	->addIndex($installer->getIdxName('udropship/vendor', array('vendor_id')),
		array('vendor_id'))

	// Foreign Keys
	->addForeignKey(
		$installer->getFkName('ghapi/user', 'vendor_id', 'udropship/vendor', 'vendor_id'),
		'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
	);

$installer->getConnection()->createTable($userTable);

/**
 * gh_api_session table
 */

$sessionTable = $installer->getConnection()
	->newTable($installer->getTable('ghapi/session'))
	->addColumn('session_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,
		'nullable'  => false,
		'primary'   => true,
		'unsigned'  => true,
	))

	/* gh_api_user.user_id */
	->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
		'unsigned'  => true,
		'nullable'  => false
	))

	/* sha256 */
	->addColumn('token', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
		'nullable'  => false
	))

	->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null)

	// Indexes
	->addIndex($installer->getIdxName('ghapi/user', array('user_id')),
		array('user_id'))

	->addIndex($installer->getIdxName('ghapi/user', array('token')),
		array('token'))

	// Foreign Keys
	->addForeignKey(
		$installer->getFkName('ghapi/session', 'user_id', 'ghapi/user', 'user_id'),
		'user_id', $installer->getTable('ghapi/user'), 'user_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
	);

$installer->getConnection()->createTable($sessionTable);

$installer->endSetup();

