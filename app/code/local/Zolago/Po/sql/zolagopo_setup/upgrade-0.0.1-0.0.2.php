<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


$poTabel = $this->getTable("udpo/po");
$poGridTabel = $this->getTable("udpo/po_grid");
$defultPosColumn = "default_pos_id";
$defultPosNameColumn = "default_pos_name";

// Add default_pos_name field to PO
$installer->getConnection()->addColumn($poTabel, $defultPosNameColumn, array(
	"type" => Varien_Db_Ddl_Table::TYPE_TEXT,
	"comment" => $defultPosNameColumn,
	"length" => 100
));

// Add defualt_pos_id, default_pos_name field to PO GRID
$installer->getConnection()->addColumn($poGridTabel, $defultPosColumn, array(
	"type" => Varien_Db_Ddl_Table::TYPE_INTEGER,
	"comment" => $defultPosColumn
));

$installer->getConnection()->addColumn($poGridTabel, $defultPosNameColumn, array(
	"type" => Varien_Db_Ddl_Table::TYPE_TEXT,
	"comment" => $defultPosNameColumn,
	"length" => 100
));

// Add index
$installer->getConnection()->addIndex(
		$poGridTabel, 
		$installer->getIdxName($poGridTabel, array($defultPosColumn)), 
		array($defultPosColumn)
);


$installer->endSetup();
