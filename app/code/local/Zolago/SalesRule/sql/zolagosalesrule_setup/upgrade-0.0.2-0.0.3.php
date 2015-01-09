<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('zolagosalesrule/relation');

$installer->getConnection()->addColumn($tableName, "simple_action", array(
	"type" => Varien_Db_Ddl_Table::TYPE_TEXT,
	"length" => 50,
	"comment" => "Action name"
));

$installer->endSetup();
