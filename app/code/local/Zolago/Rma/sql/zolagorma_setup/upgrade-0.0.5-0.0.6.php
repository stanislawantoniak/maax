<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Add add shipment data to RMA
$rmaTable = $installer->getTable("urma/rma");

$installer->getConnection()->addColumn($rmaTable, 'carrier_date', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_DATE,
	"comment"	=> "Carrier date",
));
$installer->getConnection()->addColumn($rmaTable, 'carrier_time_from', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_TEXT,
	"comment"	=> "Carrier time from",
	"length"	=> 10
));
$installer->getConnection()->addColumn($rmaTable, 'carrier_time_to', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_TEXT,
	"comment"	=> "Carrier time to",
	"length"	=> 10
));
$installer->getConnection()->addColumn($rmaTable, 'customer_account', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_TEXT,
	"comment"	=> "Customer account",
	"length"	=> 100
));


$installer->endSetup();
