<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('zolagobanner/banner');
$installer->getConnection()
    ->changeColumn($table, "type", "type", array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "nullable" => false,
        "lenght" => 50,
        "comment" => "Banner Type"
    ));

$installer->endSetup();
