<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('zolagocampaign/campaign');

$this->getConnection()
    ->addColumn($table, 'context_vendor_id', array(
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable' => false,
        'default' => 0,
        "comment" => "Landing page vendor context (vendor_id)"
    ));
$installer->getConnection()
    ->dropColumn($table, "url_key");

$installer->endSetup();