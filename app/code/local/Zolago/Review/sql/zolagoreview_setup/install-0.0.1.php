<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$reviewDetailTable = $installer->getTable("review/review_detail");

$installer->getConnection()->addColumn($reviewDetailTable, "recommend_product", array(
    "type" => Varien_Db_Ddl_Table::TYPE_INTEGER,
    "nullable" => false,
    "default" => '0',
    "comment" => "Recommend product"
));
$installer->endSetup();
