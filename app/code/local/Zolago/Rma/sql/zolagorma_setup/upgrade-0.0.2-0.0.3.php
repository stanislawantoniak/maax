<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Add add shipment data to RMA
$rmaTable = $installer->getTable("urma/rma");

$installer->getConnection()->addColumn($rmaTable, 'shipment_id', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_INTEGER,
	"comment"	=> "Shipment id"
));

$installer->getConnection()->addColumn($rmaTable, 'shipment_increment_id', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght"	=> 50,
	"comment"	=> "Shipment increment id"	
));


$installer->endSetup();
