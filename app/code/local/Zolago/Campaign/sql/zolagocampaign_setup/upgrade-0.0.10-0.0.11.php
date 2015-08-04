<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('zolagocampaign/campaign');
$installer->getConnection()
    ->changeColumn($table, "url_type", "landing_page_context", array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "nullable" => false,
        "lenght" => 15,
        "comment" => "Landing Page Context (Vendor or Gallery)"
    ));


$this->getConnection()
    ->addColumn($table, 'landing_page_category', array(
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable' => false,
        'default' => 0,
        "comment" => "Landing Page Category"
    ));

$this->getConnection()
    ->addColumn($table, 'is_landing_page', array(
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable' => false,
        'default' => 0,
        "comment" => "Is Landing Page"
    ));


$installer->endSetup();