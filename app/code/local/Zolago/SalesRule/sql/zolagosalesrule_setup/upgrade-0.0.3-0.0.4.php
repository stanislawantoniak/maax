<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('zolagosalesrule/relation');

// Drop old FKs
$installer->getConnection()->dropForeignKey($tableName, 
		$installer->getFkName('zolagosalesrule/relation', 'rule_id', 'salesrule/rule', 'rule_id'));
$installer->getConnection()->dropForeignKey($tableName, 
		$installer->getFkName('zolagosalesrule/relation', 'order_item_id', 'sales/order_item', 'item_id'));
$installer->getConnection()->dropForeignKey($tableName, 
		$installer->getFkName('zolagosalesrule/relation', 'po_item_id', 'udpo/po_item', 'entity_id'));

// Drop index
$installer->getConnection()->dropIndex($tableName, $installer->getIdxName(
		'salesrule/rule', 
		array('rule_id', 'order_item_id', 'po_item_id')
));

// Set null column
$installer->getConnection()->modifyColumn($tableName, "rule_id", array(
	"type" => Varien_Db_Ddl_Table::TYPE_INTEGER,
	'unsigned'  => true,
	'nullable'  => true,
));

// Add index (normal)
$installer->getConnection()->addIndex(
	$tableName, $installer->getIdxName('salesrule/rule', array('rule_id')), array('rule_id'));
$installer->getConnection()->addIndex(
	$tableName, $installer->getIdxName('salesrule/rule', array('order_item_id')), array('order_item_id'));
$installer->getConnection()->addIndex(
	$tableName, $installer->getIdxName('salesrule/rule', array('po_item_id')), array('po_item_id'));

// Add new FK
$installer->getConnection()->addForeignKey(
	$installer->getFkName('zolagosalesrule/relation', 'rule_id', 'salesrule/rule', 'rule_id'),
	$tableName, 'rule_id', $installer->getTable('salesrule/rule'), 'rule_id',
	Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE
);

$installer->getConnection()->addForeignKey(
	$installer->getFkName('zolagosalesrule/relation', 'order_item_id', 'sales/order_item', 'item_id'),
	$tableName, 'order_item_id', $installer->getTable('sales/order_item'), 'item_id',
	Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
);

$installer->getConnection()->addForeignKey(
	$installer->getFkName('zolagosalesrule/relation', 'po_item_id', 'udpo/po_item', 'entity_id'),
	$tableName, 'po_item_id', $installer->getTable('udpo/po_item'), 'entity_id',
	Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE
);

$installer->endSetup();
