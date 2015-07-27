<?php
/**
 * Add shipped date to RMA tracking
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$track = $installer->getTable('urma/rma_track');


$installer->getConnection()->addColumn($track, "shipped_date", array(
    "type" => Varien_Db_Ddl_Table::TYPE_DATE,
    "nullable" => true,
    "comment" => "Shipped at"
));

$installer->getConnection()->addColumn($track, "delivered_date", array(
    "type" => Varien_Db_Ddl_Table::TYPE_DATE,
    "nullable" => true,
    "comment" => "Delivered at"
));

$installer->endSetup();
