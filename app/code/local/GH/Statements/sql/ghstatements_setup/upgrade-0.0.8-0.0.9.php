<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * update order and rma track table for statements
 */

$installer->getConnection()
	->modifyColumn(
		$this->getTable("sales/shipment"),
		"statement_id",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
			'nullable'  => true,
			'comment'   => 'Statement Id'
		)
	);

$installer->getConnection()
	->modifyColumn(
		$this->getTable("urma/rma"),
		"statement_id",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
			'nullable'  => true,
			'comment'   => 'Statement Id'
		)
	);

$installer->getConnection()
	->modifyColumn(
		$this->getTable("udpo/po"),
		"statement_id",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
			'nullable'  => true,
			'comment'   => 'Statement Id'
		)
	);

$installer->getConnection()
	->dropColumn(
		$this->getTable("udpo/po"),
		"statement_date"
	);

$installer->getConnection()
	->dropColumn(
		$this->getTable("urma/rma"),
		"statement_date"
	);

$installer->getConnection()
	->dropColumn(
		$this->getTable("sales/shipment"),
		"statement_date"
	);

$installer->getConnection()
	->addForeignKey(
		$installer->getFkName('sales/shipment', 'statement_id', 'ghstatements/statement', 'id'), //$fkName
		$installer->getTable('sales/shipment'), //$tableName
		'statement_id', //$columnName
		$installer->getTable('ghstatements/statement'), //$refTableName
		'id', //$refColumnName
		Varien_Db_Ddl_Table::ACTION_SET_NULL,
		Varien_Db_Ddl_Table::ACTION_NO_ACTION
	);

$installer->getConnection()
	->addForeignKey(
		$installer->getFkName('sales/shipment_track', 'statement_id', 'ghstatements/statement', 'id'), //$fkName
		$installer->getTable('sales/shipment_track'), //$tableName
		'statement_id', //$columnName
		$installer->getTable('ghstatements/statement'), //$refTableName
		'id', //$refColumnName
		Varien_Db_Ddl_Table::ACTION_SET_NULL,
		Varien_Db_Ddl_Table::ACTION_NO_ACTION
	);

$installer->getConnection()
	->addForeignKey(
		$installer->getFkName('ghstatements/refund', 'statement_id', 'ghstatements/statement', 'id'), //$fkName
		$installer->getTable('ghstatements/refund'), //$tableName
		'statement_id', //$columnName
		$installer->getTable('ghstatements/statement'), //$refTableName
		'id', //$refColumnName
		Varien_Db_Ddl_Table::ACTION_SET_NULL,
		Varien_Db_Ddl_Table::ACTION_NO_ACTION
	);

$installer->getConnection()
	->addForeignKey(
		$installer->getFkName('urma/rma_track', 'statement_id', 'ghstatements/statement', 'id'), //$fkName
		$installer->getTable('urma/rma_track'), //$tableName
		'statement_id', //$columnName
		$installer->getTable('ghstatements/statement'), //$refTableName
		'id', //$refColumnName
		Varien_Db_Ddl_Table::ACTION_SET_NULL,
		Varien_Db_Ddl_Table::ACTION_NO_ACTION
	);

$installer->getConnection()
	->addForeignKey(
		$installer->getFkName('udpo/po', 'statement_id', 'ghstatements/statement', 'id'), //$fkName
		$installer->getTable('udpo/po'), //$tableName
		'statement_id', //$columnName
		$installer->getTable('ghstatements/statement'), //$refTableName
		'id', //$refColumnName
		Varien_Db_Ddl_Table::ACTION_SET_NULL,
		Varien_Db_Ddl_Table::ACTION_NO_ACTION
	);

$installer->getConnection()
	->addForeignKey(
		$installer->getFkName('urma/rma', 'statement_id', 'ghstatements/statement', 'id'), //$fkName
		$installer->getTable('urma/rma'), //$tableName
		'statement_id', //$columnName
		$installer->getTable('ghstatements/statement'), //$refTableName
		'id', //$refColumnName
		Varien_Db_Ddl_Table::ACTION_SET_NULL,
		Varien_Db_Ddl_Table::ACTION_NO_ACTION
	);

$installer->endSetup();
