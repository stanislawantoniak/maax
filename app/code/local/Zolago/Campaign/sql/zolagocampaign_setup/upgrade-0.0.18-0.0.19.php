<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('zolagocampaign/campaign');

$this->getConnection()
    ->addColumn($table, 'campaign_url', array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "nullable" => false,
        "lenght" => 15,
        "comment" => "Campaign Url"
    ));


$installer->endSetup();