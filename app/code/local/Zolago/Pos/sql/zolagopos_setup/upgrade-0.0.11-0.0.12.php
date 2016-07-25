<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$posTable = $installer->getTable("zolagopos/pos");

$installer->getConnection()->addColumn($posTable, "map_notes", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"comment" => "Notes in POS on map"
));

$installer->endSetup();
