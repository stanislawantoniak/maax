<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Add add shipment data to RMA
$rmaTable = $installer->getTable("urma/rma");

$installer->getConnection()->addColumn($rmaTable, 'comment_text', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_TEXT,
	"comment"	=> "Rma comment"	
));


$installer->endSetup();
