<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$vendorTable = $this->getTable("udropship/vendor");

$installer->getConnection()->addColumn($vendorTable, "legal_entity", array(
	"type" => Varien_Db_Ddl_Table::TYPE_TEXT,
	"comment" => "Vendor Legal Entity",
	"nullable" => false,
	"default" => ''
));


$installer->endSetup();