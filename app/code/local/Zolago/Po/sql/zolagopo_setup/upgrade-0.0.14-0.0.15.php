<?php
/**
 * Add custoemr emil to po
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$poTabel = $this->getTable("udpo/po");
$poTabelGrid = $this->getTable("udpo/po_grid");

// Grand total
$installer->getConnection()->addColumn($poTabel, "customer_email", array(
	"type" => Varien_Db_Ddl_Table::TYPE_TEXT,           
	"length" => 100,
	"comment" => "customer_email"
));
$installer->getConnection()->addColumn($poTabelGrid, "customer_email", array(
	"type" => Varien_Db_Ddl_Table::TYPE_TEXT,           
	"length" => 100,
	"comment" => "customer_email"
));



$installer->endSetup();
