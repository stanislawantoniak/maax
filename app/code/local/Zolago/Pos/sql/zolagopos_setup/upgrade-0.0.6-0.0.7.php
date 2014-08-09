<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Add DHL login data to POS
$posTable = $installer->getTable("zolagopos/pos");
$installer->getConnection()->addColumn($posTable, "use_orbaups", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_INTEGER,
	"lenght" => 1,
	"comment" => "Use UPS"
));
$installer->getConnection()->addColumn($posTable, "orbaups_login", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "UPS Login"
));
$installer->getConnection()->addColumn($posTable, "orbaups_password", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "UPS Password"
));
$installer->getConnection()->addColumn($posTable, "orbaups_account", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "UPS License key"
));

// Add DHL login data to Vendor
$vendorTable = $installer->getTable("udropship/vendor");
$installer->getConnection()->addColumn($vendorTable, "use_orbaups", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_INTEGER,
	"lenght" => 1,
	"comment" => "Use UPS"
));
$installer->getConnection()->addColumn($vendorTable, "orbaups_login", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "UPS Login"
));
$installer->getConnection()->addColumn($vendorTable, "orbaups_password", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "UPS Password"
));
$installer->getConnection()->addColumn($vendorTable, "orbaups_account", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "UPS License key"
));
$installer->getConnection()->addColumn($vendorTable, "orbaups_rma_login", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "UPS RMA Login"
));
$installer->getConnection()->addColumn($vendorTable, "orbaups_rma_password", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "UPS RMA Password"
));
$installer->getConnection()->addColumn($vendorTable, "orbaups_rma_account", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "UPS RMA License key"
));

$installer->endSetup();
