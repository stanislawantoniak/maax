<?php
/**
 * Add current carrier to po
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$poTabel = $this->getTable("udpo/po");
$poTabelGrid = $this->getTable("udpo/po_grid");

// Grand total
$installer->getConnection()->addColumn($poTabel, "current_carrier", array(
	"type" => Varien_Db_Ddl_Table::TYPE_TEXT,           
	"length" => 100,
	"comment" => "current_carrier"
));
$installer->getConnection()->addColumn($poTabelGrid, "current_carrier", array(
	"type" => Varien_Db_Ddl_Table::TYPE_TEXT,           
	"length" => 100,
	"comment" => "current_carrier"
));

$installer->endSetup();
