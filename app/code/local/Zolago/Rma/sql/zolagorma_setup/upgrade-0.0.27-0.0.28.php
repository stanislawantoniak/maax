<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$tableName = $installer->getTable("urma/rma");

$installer->getConnection()->addColumn($tableName, 'returned_shipping_value', array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_DECIMAL,
	"length"		=> "12,4",
	"comment"	=> "Returned shipping amount",
	"default" 	=> 0,
	"nullable" 	=> false,
));

$installer->endSetup();