<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$vendorTable = $this->getTable("udropship/vendor");

$installer->getConnection()->addColumn($vendorTable, "last_integration", array(
	"type" => Varien_Db_Ddl_Table::TYPE_DATETIME,
	"comment" => "Last integration date",
	"nullable" => true,
	"default" => null
));

$installer->endSetup();
