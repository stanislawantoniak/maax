<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$posTable = $installer->getTable("zolagopos/pos");

$installer->getConnection()->addColumn($posTable, "priority", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_INTEGER,
	"nullable"=>false, 
	"default"=>1,
	"comment" => "Priority"
));

$installer->endSetup();
