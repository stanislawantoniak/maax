<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$vendorTable = $this->getTable("udropship/vendor");

$installer->getConnection()->addColumn($vendorTable, "super_vendor_id", array(
	"type" => Varien_Db_Ddl_Table::TYPE_INTEGER,    
	"comment" => "Super Vendor Id",
	"nullable" => true
));

$installer->getConnection()->addIndex(
		$vendorTable, 
		$installer->getIdxName($vendorTable, array("super_vendor_id")), 
		array("super_vendor_id")
);

$installer->endSetup();

