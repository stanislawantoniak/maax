<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$poTabel = $this->getTable("udpo/po");
$poTabelGrid = $this->getTable("udpo/po_grid");

// Stock confirm
$installer->getConnection()->addColumn($poTabel, "stock_confirm", array(
	"type" => Varien_Db_Ddl_Table::TYPE_BOOLEAN,    
	"comment" => "Stock confirm"
));
$installer->getConnection()->addColumn($poTabelGrid, "stock_confirm", array(
	"type" => Varien_Db_Ddl_Table::TYPE_DECIMAL,         
	"comment" => "Stock confirm"
));

$installer->endSetup();
