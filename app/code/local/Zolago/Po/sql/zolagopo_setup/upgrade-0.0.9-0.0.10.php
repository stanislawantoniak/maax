<?php
/**
 * Add pos field to aggregated
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$posTable = $this->getTable("zolagopos/pos");
$aggregatedTable = $this->getTable("zolagopo/aggregated");

$installer->getConnection()->addColumn($aggregatedTable, "pos_id", array(
	"type" => Varien_Db_Ddl_Table::TYPE_INTEGER,    
	"comment" => "Pos Id",
	"nullable" => false
));

$installer->getConnection()->addIndex(
		$aggregatedTable, $installer->getIdxName($aggregatedTable, array("pos_id")), array("pos_id"));

$installer->getConnection()->addForeignKey(
	$installer->getFkName($aggregatedTable, "pos_id", $posTable, "pos_id"), 
	$aggregatedTable, "pos_id", $posTable, "pos_id", 
	Varien_Db_Adapter_Interface::FK_ACTION_CASCADE, Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
);


$installer->endSetup();
