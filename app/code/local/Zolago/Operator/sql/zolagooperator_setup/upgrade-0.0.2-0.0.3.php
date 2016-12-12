<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$operatorTable = $installer->getTable("zolagooperator/operator");

$installer->getConnection()->addColumn($operatorTable, "dhl_label_type", array(
	"type"=>  Varien_Db_Ddl_Table::TYPE_TEXT,
	"length"=> 255,
	"comment"=>"Dhl label type",
	"default" => Orba_Shipping_Model_System_Source_Carrier_Dhl_Label::LP,
));

$installer->endSetup();
