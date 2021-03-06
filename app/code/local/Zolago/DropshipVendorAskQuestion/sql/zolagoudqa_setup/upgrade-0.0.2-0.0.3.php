<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$questionTable = $this->getTable("udqa/question");

// Customer can ask vendor?
$installer->getConnection()->addColumn($questionTable, "po_id", array(
    "type"     => Varien_Db_Ddl_Table::TYPE_INTEGER,
    "comment"  => "assign to po",
    "nullable" => true,
));


$installer->endSetup();
