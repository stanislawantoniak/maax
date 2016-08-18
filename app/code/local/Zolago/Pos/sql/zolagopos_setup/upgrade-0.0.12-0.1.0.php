<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$posTable = $installer->getTable("zolagopos/pos");
$installer->getConnection()
    ->addColumn($posTable, "is_available_as_pickup_point", array(
        "type" => Varien_Db_Ddl_Table::TYPE_INTEGER,
        "nullable" => false,
        "default" => 0,
        "length" => 1,
        "comment" => "Is POS available as Pick-Up Point"
    ));

$installer->endSetup();
