<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$vendorTable = $this->getTable("udropship/vendor");
$installer->getConnection()->addColumn($vendorTable, 'regulation_accept_document_date', 'datetime default NULL');
$installer->getConnection()->addColumn($vendorTable, "regulation_accept_document_data", array(
    "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
    "comment" => "Regulation accept document data",
    "nullable" => false,
    "default" => ''
));
$installer->endSetup();