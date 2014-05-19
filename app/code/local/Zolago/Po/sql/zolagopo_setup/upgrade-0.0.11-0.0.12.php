<?php
/**
 * Add pos field to aggregated
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$vendorTable = $this->getTable("udropship/vendor");
$aggregatedTable = $this->getTable("zolagopo/aggregated");

$installer->getConnection()->addColumn($aggregatedTable, "status", array(
	"type" => Varien_Db_Ddl_Table::TYPE_BOOLEAN,    
	"comment" => "Status",
	"default" => 0,
	"nullable" => false
));



$installer->endSetup();
