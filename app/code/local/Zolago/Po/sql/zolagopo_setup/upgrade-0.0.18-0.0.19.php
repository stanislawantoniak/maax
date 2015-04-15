<?php
/**
 * Add reservation flag
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$poTable = $this->getTable("udpo/po");

// Grand total
$installer->getConnection()->addColumn($poTable, "reservation", array(
    "type" => Varien_Db_Ddl_Table::TYPE_INTEGER,
    "comment" => "Reservation flag",
    "nullable" => true,
    "default" => 1
));

$installer->endSetup();