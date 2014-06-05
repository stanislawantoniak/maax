<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Add another RMA account to Vendor
$vendorTable = $installer->getTable("udropship/vendor");
$installer->getConnection()->addColumn($vendorTable, "dhl_rma_account", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "DHL RMA account number"
));
$installer->getConnection()->addColumn($vendorTable, "dhl_rma_login", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "Login for DHL RMA account"
));
$installer->getConnection()->addColumn($vendorTable, "dhl_rma_password", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 32,
	"comment" => "Password for DHL RMA account"
));


$installer->endSetup();
