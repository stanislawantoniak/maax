<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$vendorTable = $this->getTable("udropship/vendor");

$installer->getConnection()->addColumn($vendorTable, "vendor_type", array(
        "type" => Varien_Db_Ddl_Table::TYPE_INTEGER,
        "comment" => "Vendor Type",
        "nullable" => false,
        "default" => '1'
                                       ));


$installer->endSetup();



