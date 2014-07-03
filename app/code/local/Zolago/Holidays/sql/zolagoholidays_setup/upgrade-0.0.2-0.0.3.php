<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$tableName = $installer->getTable('udropship/vendor');



$installer->getConnection()->addColumn($tableName, "max_shipping_date", array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_INTEGER,
	"length"	=> 2,
	"comment"	=> "Max shipping date"
));
		
$installer->endSetup();