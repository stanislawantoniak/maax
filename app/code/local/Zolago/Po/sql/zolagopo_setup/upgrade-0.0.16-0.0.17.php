<?php
/**
 * Add shipped date to tracking
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$poTabel = $this->getTable("udpo/po");

// Grand total
$installer->getConnection()->addColumn($poTabel, "max_shipping_date", array(
	"type" => Varien_Db_Ddl_Table::TYPE_DATE,
	"nullable" => true,
	"comment" => "Maximum Shipping Date"
));

$installer->endSetup();
