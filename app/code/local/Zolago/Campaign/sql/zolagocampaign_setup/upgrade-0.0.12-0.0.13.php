<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('zolagocampaign/campaign');


$installer->getConnection()
    ->changeColumn($table, "landing_page_context", "landing_page_context", array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "nullable" => false,
        "lenght" => 50,
        "comment" => "Landing Page Context (Vendor or Gallery)"
    ));


$installer->endSetup();