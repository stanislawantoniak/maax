<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Add DHL login data to POS
$posTable = $installer->getTable("zolagopos/pos");
$installer->getConnection()->addColumn($posTable, "dhl_ecas", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "DHL eCas Id"
));
$installer->getConnection()->addColumn($posTable, "dhl_terminal", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 3,
	"comment" => "DHL Terminal code"
));

// Add DHL login data to Vendor
$vendorTable = $installer->getTable("udropship/vendor");
$installer->getConnection()->addColumn($vendorTable, "dhl_ecas", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "DHL eCas Id"
));
$installer->getConnection()->addColumn($vendorTable, "dhl_terminal", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 3,
	"comment" => "DHL Terminal code"
));


$installer->endSetup();
