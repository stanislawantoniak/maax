<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Add type to trakcing
$rmaTrackTable = $installer->getTable("urma/rma_track");

$installer->getConnection()->addColumn($rmaTrackTable, 'track_creator', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_INTEGER,
	"comment"	=> "Track type",
	"length"	=> 1,
	"default"	=> 0
));


$installer->endSetup();
