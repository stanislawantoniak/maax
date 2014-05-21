<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$vendorTable = $installer->getTable("udropship/vendor");

$installer->getConnection()->addColumn($vendorTable, "external_id", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 64,
	"comment" => "External Id"
));

$installer->endSetup();
