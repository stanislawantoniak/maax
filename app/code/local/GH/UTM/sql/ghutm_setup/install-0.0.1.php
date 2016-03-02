<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $this->getTable("admin_user");

$installer->getConnection()->addColumn($table, "utm_data", array(
    "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
    "comment" => "UTM Data",
    "nullable" => true,
    "default" => null
));


$installer->endSetup();