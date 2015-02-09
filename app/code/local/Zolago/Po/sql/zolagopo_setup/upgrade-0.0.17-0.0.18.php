<?php
/**
 * Add shipped date to tracking
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$track = $this->getTable("sales/shipment_track");

// Grand total
$installer->getConnection()->addColumn($track, "delivered_date", array(
	"type" => Varien_Db_Ddl_Table::TYPE_DATE,
	"nullable" => true,
	"comment" => "Delivered at"
));

$installer->endSetup();
