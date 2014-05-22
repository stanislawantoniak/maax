<?php
/**
 * Add sequence nr to aggregated
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$aggregatedTable = $this->getTable("zolagopo/aggregated");

$installer->getConnection()->addColumn($aggregatedTable, "sequence", array(
	"type" => Varien_Db_Ddl_Table::TYPE_INTEGER,    
	"comment" => "Sequence",
	"default" => 1,
	"nullable" => false
));



$installer->endSetup();
