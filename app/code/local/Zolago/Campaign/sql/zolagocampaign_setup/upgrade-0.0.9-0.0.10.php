<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('zolagocampaign/campaign_product');
$this->getConnection()
    ->addColumn($table, 'assigned_to_campaign', array(
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable' => false,
        'default' => 0,
        "comment" => "Flag is product (campaign product attributes set) assigned to campaign"
    ));

$installer->endSetup();