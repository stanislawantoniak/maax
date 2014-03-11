<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Add DHL login data to POS
$posTable = $installer->getTable("zolagopos/pos");
$installer->getConnection()->addColumn($posTable, "use_dhl", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_INTEGER,
	"lenght" => 1,
	"comment" => "Use DHL"
));
$installer->getConnection()->addColumn($posTable, "dhl_login", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "DHL Login"
));
$installer->getConnection()->addColumn($posTable, "dhl_password", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "DHL Password"
));

// Add DHL login data to Vendor
$vendorTable = $installer->getTable("udropship/vendor");
$installer->getConnection()->addColumn($vendorTable, "use_dhl", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_INTEGER,
	"lenght" => 1,
	"comment" => "Use DHL"
));
$installer->getConnection()->addColumn($vendorTable, "dhl_login", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "DHL Login"
));
$installer->getConnection()->addColumn($vendorTable, "dhl_password", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "DHL Password"
));


$installer->endSetup();
