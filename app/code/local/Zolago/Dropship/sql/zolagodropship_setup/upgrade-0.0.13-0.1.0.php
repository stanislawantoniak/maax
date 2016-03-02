<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$vendorTable = $this->getTable("udropship/vendor");

$installer->getConnection()
    ->modifyColumn($vendorTable,                    //$tableName
        'created_at',                              //$oldColumnName
        'datetime default 0'                     //$definition
    );

$installer->getConnection()->addColumn($vendorTable, "updated_at", array(
    "type" => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    "comment" => "Updated At",
    "nullable" => false,
    "default"  => Varien_Db_Ddl_Table::TIMESTAMP_INIT_UPDATE
));

$installer->getConnection()->addColumn($vendorTable,"integrator_secret", array(
    "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
    "comment" => "Integrator Secret",
    "length" => "32",
    "nullable" => false,
    "default" => ""
));

$installer->endSetup();