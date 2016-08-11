<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$vendorTable = $this->getTable("udropship/vendor");

$installer->getConnection()->addColumn($vendorTable, "regulation_accepted", array(
    "type" => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    "comment" => "Regulation accepted by vendor",
    "nullable" => false,
    "default" => false
));
$installer->endSetup();