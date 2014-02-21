<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$operatorTable = $installer->getTable("zolagooperator/operator");

$installer->getConnection()->addColumn($operatorTable, "roles", array(
	"type"=>  Varien_Db_Ddl_Table::TYPE_TEXT,
	"length"=> 255,
	"comment"=>"Roles"
));

$installer->endSetup();
