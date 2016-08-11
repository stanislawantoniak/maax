<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('zolagocampaign/campaign');
$installer->getConnection()
    ->dropColumn($table, "landing_page_context");


$this->getConnection()
    ->addColumn($table, 'landing_page_context', array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "nullable" => false,
        "lenght" => 15,
        "comment" => "Landing Page Context (Vendor or Gallery)"
    ));


$installer->endSetup();