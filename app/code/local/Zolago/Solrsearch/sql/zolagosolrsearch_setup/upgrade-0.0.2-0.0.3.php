<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


/**
 * Product queue - add index type
 */

$table = $installer->getTable('zolagosolrsearch/queue_item');

$installer->getConnection()
    ->addColumn($table, "delete_only", array(
		"type" => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
		"default" => 0,
		"comment" => "Shoul be only cleanup?"
	));
$installer->getConnection()
    ->addIndex($table, $installer->getIdxName($table, array("delete_only")), array("delete_only"));

$installer->endSetup();
