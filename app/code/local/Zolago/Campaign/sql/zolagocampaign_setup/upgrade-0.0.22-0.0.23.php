<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('zolagocampaign/campaign');


$this->getConnection()
    ->addColumn($table, 'coupon_image', array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "nullable" => false,
        "lenght" => 255,
        "comment" => "Campaign Image For Coupon"
    ));
$this->getConnection()
    ->addColumn($table, 'coupon_conditions', array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "nullable" => false,
        "lenght" => 255,
        "comment" => "Campaign: Terms and Conditions For Coupon"
    ));


$installer->endSetup();

