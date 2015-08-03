<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('zolagocampaign/campaign');

$installer->getConnection()
    ->addColumn($table, "landing_page_url", array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "nullable" => false,
        "comment" => "Landing Page URL"
    ));

$installer->endSetup();