<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$vendorTable = $this->getTable("udropship/vendor");

$installer->getConnection()->addColumn($vendorTable, "cpc_commission", array(
    "type" => Varien_Db_Ddl_Table::TYPE_FLOAT,
    "comment" => "Vendor Legal Entity",
    "nullable" => true,
    "default" => null
));


$installer->endSetup();