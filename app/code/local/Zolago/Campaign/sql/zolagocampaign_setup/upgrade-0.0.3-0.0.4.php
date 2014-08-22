<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('zolagocampaign/campaign');
$this->getConnection()
    ->addColumn($table, 'name_customer', array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "nullable" => false,
        "lenght" => 100,
        "comment" => "Campaign name for customers"
    ));
$this->getConnection()
    ->addColumn($table, 'url_type', array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "nullable" => false,
        "lenght" => 15,
        "comment" => "Url type: landing page or manual link"
    ));
$installer->endSetup();




