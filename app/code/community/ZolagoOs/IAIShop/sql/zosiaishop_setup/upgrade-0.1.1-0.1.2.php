<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$poTable = $installer->getTable("udpo/po");

$installer->getConnection()->addColumn($poTable, "external_payment_id", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 64,
	"comment" => "External Payment Id"
));

$installer->endSetup();
