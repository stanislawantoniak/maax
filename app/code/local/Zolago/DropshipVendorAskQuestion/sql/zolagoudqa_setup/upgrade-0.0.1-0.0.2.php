<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$vendorTable = $this->getTable("udropship/vendor");

// vendors on mainpage in customized sequence (order)
$installer->getConnection()->addColumn($vendorTable, "can_ask", array(
    "type"     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    "lenght"   => 1,
    "comment"  => "Customer can ask?",
    "nullable" => false,
    "default"  => 1 // Default yes
));

// vendors on mainpage in customized sequence (order)
$installer->getConnection()->addColumn($vendorTable, "sequence", array(
    "type"     => Varien_Db_Ddl_Table::TYPE_INTEGER,
    "lenght"   => 11,
    "comment"  => "Sequence (Order) for show on front",
    "nullable" => false,
    "default"  => 10000 // At the end
));

$installer->endSetup();
