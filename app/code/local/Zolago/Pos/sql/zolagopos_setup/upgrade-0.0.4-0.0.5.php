<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Add DHL login data to Vendor
$vendorTable = $installer->getTable("udropship/vendor");
$installer->getConnection()->addColumn($vendorTable, "review_status", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_INTEGER,
	"lenght" => 6,
	"comment" => "Review Status"
));

$installer->endSetup();
