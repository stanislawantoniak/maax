<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$questionTable = $this->getTable("udqa/question");

// store id from questions
$installer->getConnection()->addColumn($questionTable, "store_id", array(
    "type"     => Varien_Db_Ddl_Table::TYPE_INTEGER,
    "comment"  => "assign to store_id",
    "nullable" => true,
));


$installer->endSetup();
