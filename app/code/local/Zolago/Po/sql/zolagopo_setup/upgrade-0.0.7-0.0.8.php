<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$poTabel = $this->getTable("udpo/po");
$poTabelGrid = $this->getTable("udpo/po_grid");

// Stock confirm
$installer->getConnection()->addColumn($poTabel, "alert", array(
	"type" => Varien_Db_Ddl_Table::TYPE_INTEGER,    
	"comment" => "Alert",
	"nullable" => true
));
$installer->getConnection()->addColumn($poTabelGrid, "alert", array(
	"type" => Varien_Db_Ddl_Table::TYPE_INTEGER,         
	"comment" => "Alert",
	"nullable" => true
));

$installer->endSetup();
