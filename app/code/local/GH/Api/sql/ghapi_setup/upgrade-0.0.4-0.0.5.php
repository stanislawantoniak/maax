<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


/**
 * Change is_active to status
 */
$poTable = $installer->getTable("udpo/po");

$installer->getConnection()->addColumn($poTable, "external_order_id", array(
	"type"=>Varien_Db_Ddl_Table::TYPE_TEXT,
	"lenght" => 64,
	"comment" => "External Order Id"
));

$installer->endSetup();
