<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Add Map fields
$posTable = $installer->getTable("zolagopos/pos");
$installer->getConnection()
    ->addColumn($posTable, "show_on_map", array(
        "type"=>Varien_Db_Ddl_Table::TYPE_INTEGER,
        "nullable"=>false,
        "default"=> 0,
        "length" => 1,
        "comment" => "Show POS on map"
    ));
$installer->getConnection()
    ->addColumn($posTable, "map_name", array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "comment" => "POS name to show on map"
    ));

$installer->getConnection()
    ->addColumn($posTable, "map_latitude", array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "length" => 32,
        "comment" => "Latitude"
    ));

$installer->getConnection()
    ->addColumn($posTable, "map_longitude", array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "length" => 32,
        "comment" => "Longitude"
    ));

$installer->getConnection()
    ->addColumn($posTable, "map_phone", array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "length" => 32,
        "comment" => "Phone to show on map"
    ));

$installer->getConnection()
    ->addColumn($posTable, "map_time_opened", array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "comment" => "POS time opened to show on map"
    ));

$installer->endSetup();
