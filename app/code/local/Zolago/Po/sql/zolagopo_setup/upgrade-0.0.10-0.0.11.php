<?php
/**
 * Add pos field to aggregated
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$vendorTable = $this->getTable("udropship/vendor");
$aggregatedTable = $this->getTable("zolagopo/aggregated");

$installer->getConnection()->addColumn($aggregatedTable, "vendor_id", array(
	"type" => Varien_Db_Ddl_Table::TYPE_INTEGER,    
	"comment" => "Vendor Id",
	"nullable" => false
));

$installer->getConnection()->addIndex(
		$aggregatedTable, $installer->getIdxName($aggregatedTable, array("vendor_id")), array("vendor_id"));

$installer->getConnection()->addForeignKey(
	$installer->getFkName($aggregatedTable, "vendor_id", $vendorTable, "vendor_id"), 
	$aggregatedTable, "vendor_id", $vendorTable, "vendor_id", 
	Varien_Db_Adapter_Interface::FK_ACTION_CASCADE, Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
);


$installer->endSetup();
