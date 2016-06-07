<?php
/* Basic structure **/

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


/**
 * Attach orders token
 */
$table = $installer->getConnection()
	->newTable($installer->getTable('zolagocustomer/attachtoken'))
	->addColumn("token_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,
		'nullable'  => false,
		'primary'   => true,
	))
	->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned'  => true,
	), 'Customer ID')
	->addColumn('token', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
		'nullable'  => false,
	), 'Token')
	->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
	), 'Creation Time')
	->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
	), 'Update Time')
	->addIndex($installer->getIdxName('zolagocustomer/emailtoken', array('customer_id')),
		array('customer_id'))
	->addForeignKey(
		$installer->getFkName('zolagocustomer/attachtoken', 'customer_id', 'customer/entity','entity_id'),
		'customer_id', $installer->getTable('customer/entity'), 'entity_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

$installer->getConnection()->createTable($table);
$installer->endSetup();
