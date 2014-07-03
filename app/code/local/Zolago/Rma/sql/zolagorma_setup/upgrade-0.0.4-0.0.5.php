<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Add relations anc author name
$rmaCommentTable = $installer->getTable("urma/rma_comment");

$installer->getConnection()->addColumn($rmaCommentTable, 'customer_id', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_INTEGER,
	"comment"	=> "Customer Id",
	"nullable"  => true
));
$installer->getConnection()->addColumn($rmaCommentTable, 'operator_id', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_INTEGER,
	"comment"	=> "Operator Id",
	"nullable"  => true
));
$installer->getConnection()->addColumn($rmaCommentTable, 'vendor_id', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_INTEGER,
	"comment"	=> "Vendor Id",
	"nullable"  => true
));

$installer->getConnection()->addColumn($rmaCommentTable, 'author_name', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_TEXT,
	"length"	=> 100,
	"comment"	=> "Author name",
	"nullable"  => true
));

// Add indexes
$installer->getConnection()->addIndex($rmaCommentTable, 
		$installer->getIdxName($rmaCommentTable, array("customer_id")), array("customer_id"));

$installer->getConnection()->addIndex($rmaCommentTable, 
		$installer->getIdxName($rmaCommentTable, array("operator_id")), array("operator_id"));

$installer->getConnection()->addIndex($rmaCommentTable, 
		$installer->getIdxName($rmaCommentTable, array("vendor_id")), array("vendor_id"));

// Ad FKs
$installer->getConnection()->addForeignKey(
		$installer->getFkName($rmaCommentTable, "customer_id", $installer->getTable('customer/entity'), "entity_id"), 
		$rmaCommentTable, "customer_id", $installer->getTable('customer/entity'), "entity_id", 
		Varien_Db_Adapter_Interface::FK_ACTION_SET_NULL, Varien_Db_Adapter_Interface::FK_ACTION_CASCADE);

$installer->getConnection()->addForeignKey(
		$installer->getFkName($rmaCommentTable, "operator_id", $installer->getTable('zolagooperator/operator'), "operator_id"), 
		$rmaCommentTable, "operator_id", $installer->getTable('zolagooperator/operator'), "operator_id", 
		Varien_Db_Adapter_Interface::FK_ACTION_SET_NULL, Varien_Db_Adapter_Interface::FK_ACTION_CASCADE);

$installer->getConnection()->addForeignKey(
		$installer->getFkName($rmaCommentTable, "vendor_id", $installer->getTable('udropship/vendor'), "vendor_id"), 
		$rmaCommentTable, "vendor_id", $installer->getTable('udropship/vendor'), "vendor_id", 
		Varien_Db_Adapter_Interface::FK_ACTION_SET_NULL, Varien_Db_Adapter_Interface::FK_ACTION_CASCADE);

$installer->endSetup();
